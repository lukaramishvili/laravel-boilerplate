<?php namespace App;

use Config;
use Log;
use File;
use URL;
use Image;
//use FFMpeg\FFMpeg;
use Carbon\Carbon;
use GuzzleHttp;
use Session;
use Auth;

use App\Cart_items;
use App\Cart_items_of_agent;
use App\Forgotten_agents;
use App\Forgotten_clients;

class Helper {

    public static function isEmptyDate($date){
        if(get_class($date) == 'Carbon\Carbon'){
            return $date->year == -1;
        } else {
            return true;
        }
    }

    public static function getUTCOffset(){
        //1 means UTC+1
        return intval(\Carbon\Carbon::now()->format('Z'))/3600;
    }

    public static function addZeros($str){
        return mb_strlen($str) == 1 ? '0'.$str : $str;
    }

    public static function addScheme($url, $scheme = 'http://')
    {
        return parse_url($url, PHP_URL_SCHEME) === null ?
            $scheme . $url : $url;
    }

    public static function ini_get_in_bytes($val) {
        $val = ini_get($val);
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
        }
        return $val;
    }

    public static function maxAllowedUploadSize($desired_size){
        $post_max_size = Helper::ini_get_in_bytes('post_max_size');
        $upload_max_filesize = Helper::ini_get_in_bytes('upload_max_filesize');
        $server_max_allowed = min($post_max_size, $upload_max_filesize);
        return min($desired_size, $server_max_allowed);
    }

    /**
     * Writes out a tab-delimited, Unicode BOM-prefixed csv file (for Excel)
     * $contents should be in format array( array('cell1','cell2'), array(c1,c2..),..)
     **/
    public static function put_csv_contents($file, $rows){
        if(is_array($rows)){
            $fp = fopen($file, 'w');
            fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            foreach ($rows as $fields) {
                fputcsv($fp, $fields, chr(9));
            }
            fclose($fp);
            return true;
        } else {
            return false;
        }
    }

    public static function getEmailViewInfoFromPath($view_path){
        $name = explode('emails/', $view_path)[1];
        $name = str_replace('.blade.php', '', $name);
        //
        $contents = File::get($view_path);
        $argNames = [];
        preg_match_all('/\$[A-Za-z0-9_]+/i', $contents, $argNames);
        $argNames = $argNames[0];
        $argNames = array_map(function($a){
            $ret = $a;
            //$ret = preg_replace('/-$/', '', $ret);//eliminate ending dashes in $var->
            $ret = preg_replace('/\$/', '', $ret);// $var => var
            return $ret;
        }, $argNames);
        $argNames = array_unique($argNames);
        $testArgs = [];
        foreach($argNames as $argName){
            $argClassName = $argName;
            //using 'chat_message' because 'message' variable was reserved by Illuminate
            if($argName == 'chat_message'){ $argClassName = 'message'; }
            $argFull = "\App\\".ucfirst($argClassName);
            if(class_exists($argFull)){
                $testArgs[$argName] = $argFull::inRandomOrder()->first();
            } else {
                $testArgs[$argName] = strtoupper($argName);
            }
        }
        //
        $ret = new \stdClass;
        $ret->name = $name;
        $ret->path = $view_path;
        $ret->arguments = $argNames;
        $ret->test_arguments = $testArgs;
        return $ret;
    }

    /**
     * $t is the object returned by getEmailViewInfoFromPath
     **/
    public static function htmlPreviewEmailTemplate($t){
        return view('emails.'.$t->name, $t->test_arguments);
    }

    /**
     * pass e.g. 'emails.view-name' and it will return language-specific name
     * (e.g.) 'emails.ge.view-name'
     **/
    public static function localizeEmailTemplateName($t){
        if(lang() == "de"){
            // german translated views are in the "views/emails/de" subfolder
            return preg_replace('/^emails\./i','emails.'.lang().'.',$t);
        } else {
            return $t;
        }
    }

    /*
     * replaces e.g. en to de in /en, /en/, /X/en, /X/en/, /X/en/Y
     */
    private static function regexForUrlLangChange($new_lang){
        return '/(?<=^|\/)'.$new_lang.'(?=(\/|$))/';
    }

    /*
     * swaps one language to another in $url (e.g. /en/auth/choose to /de/auth/choose)
     */
    public static function swapUrlLang($url, $lang1, $lang2){
        return preg_replace(self::regexForUrlLangChange($lang1), $lang2, $url, 1);
    }

    /*
     * changes language code in an url
     */
    public static function urlForLang($url, $lang){
        $all_langs = '('.implode('|', array_keys(\Config::get('app.locales'))).')';
        return preg_replace(self::regexForUrlLangChange($all_langs), $lang, $url, 1);
    }


    public static function isMobile(){
        if(!isset($_SERVER) || !isset($_SERVER['HTTP_USER_AGENT'])) { return false; }
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|pad|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk|windows\ phone|iemobile|ie\ mobile|wpdesktop|nokia|xblwp7|zunewp7|lumia/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
    }

    public static function isiOS(){
        $browserAsString = $_SERVER['HTTP_USER_AGENT'];
        return (strstr($browserAsString, " AppleWebKit/") && strstr($browserAsString, " Mobile/"));
    }

    /**
     * convert date string in Georgian local time to timestamp
     */
    public static function strtotime_geo($date_string){
        return strtotime($date_string . " GMT+400");
    }

    public static function parseDate($strMysqlDate){
        return Carbon::createFromFormat('Y-m-d', $strMysqlDate);
    }

    public static function parseDatetime($strMysqlDate){
        //return Carbon::createFromFormat('Y-m-d H:i:s', $strMysqlDate);
        //simplified to parse because sometimes the date from browser contains +0400
        return Carbon::parse($strMysqlDate);
    }

    public static function parseDateEuropean($strDate){
        return Carbon::createFromFormat('d.m.Y', $strDate);
    }

    public static function parseDatetimeEuropean($strDate){
        return Carbon::createFromFormat('d.m.Y H:i:s', $strDate);
    }

    public static function formatDate($carbonDate){
        return $carbonDate->format('d.m.Y');
    }

    public static function formatDatetime($carbonDate){
        return $carbonDate->format('d.m.Y H:i:s');
    }

    public static function http_auth($username, $password){
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            //not passed credentials
            return "noneprovided";
        } else {
            if($_SERVER['PHP_AUTH_USER'] == $username
            && $_SERVER['PHP_AUTH_PW'] == $password) {
                //authentication successful
                return "success";
            }
            else {
                //credentials passed, but incorrect
                return "fail";
            }
        }
    }

    public static function inputPattern($type = "string"){
        switch($type){
        case "phone.number":
            return "(\d|\s){5,20}";
            break;
        case "phone.full_number":
            return "\+\d{5,20}";
            break;
        case "phone.full_number_piped":
            return "\+\d{1,5}\|\d{5,20}";
            break;
        case "digits":
            return "\d+";
            break;
        case "decimal":
            // this ordering of alternatives works in ie11; didn't work when \d+ was 1st
            return "(\d*\.\d+)|(\d+)";
            break;
        case "decimal.comma-allowed":
            // this ordering of alternatives works in ie11; didn't work when \d+ was 1st
            return "(\d*\.\d+)|(\d*,\d+)|(\d+)";
            break;
        case "email":
            return ".+@.+\..{2,32}";
            break;
        case "email_or_full_phone_number":
            return "(".Helper::inputPattern('email').")|"."(".Helper::inputPattern('phone.full_number').")";
            break;
        case "password.alphanumeric-up-low-number":
            //required min-7, should contain both uppercase, lowercase and a number
            return "^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])\w{7,}$";
            break;
        case "password.alphanumeric-min-four":
            //required min-7, numbers and characters (any case)
            //return "[A-Za-z0-9]{4,}";
            return "[\s\S]{4,}";
            break;
        case "password.alphanumeric":
            //required min-7, numbers and characters (any case)
            return "(?=.*\d)(?=.*[A-Za-z])\w{7,}";
            break;
        case "password.alphanum-up-down-optional-special":
            //min.7 alphanum & special chars. required 1 upper, 1 lower and 1 digit
            return "^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[\w~@#$%^&*+=`|{}:;!.?\"()\[\]-]{7,32}$";
        case "password.project-standard":
            //return Helper::inputPattern("password.alphanum-up-down-optional-special");
            return Helper::inputPattern("password.alphanumeric-min-four");
            break;
        case "sms.code":
            return "\d{4}";
            break;
        default:
            return "INCORRECT_PATTERN_NAME";
            break;
        }
    }

    public static function genSMSCode(){
            //tip: when generating codes, use Faker::regex(inputPattern('sms.code'))
        return rand(1234, 9876);
    }

    public static function genEmailCode(){
        return md5(rand(1234567890, 9876543210));
    }

    /**
     * Fixes image orientation based on EXIF tag using Laravel's Intervention Image lib
     */
    public static function fixImageOrientation($filename){
        if(File::exists($filename)){
            ini_set('memory_limit', '256M');
            try {
                $img = Image::make($filename)->orientate();
                $img->save($filename, 85);
                return true;
            } catch(\Exception $ex){
                return false;
            }
        } else {
            return false;
        }
    }

    public static function setDontShowAgain($message_key){
        $authed_user_id = Auth::check() ? Auth::user()->id : 0;
        Session::put('dont_show_again_'.'user_'.$authed_user_id.'_'.$message_key, true);
        return true;
    }

    public static function isDontShowAgainSelected($message_key){
        $authed_user_id = Auth::check() ? Auth::user()->id : 0;
        //e.g. dont_show_again_user_59_agent_new_selling_popup
        return !!Session::get('dont_show_again_'.'user_'.$authed_user_id.'_'.$message_key, false);
    }


}
