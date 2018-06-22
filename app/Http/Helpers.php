<?php

//returns current locale/language
if (! function_exists('lang')) {
    function lang(){
        return LaravelLocalization::getCurrentLocale();
    }
}

//adds language prefix to url
if (! function_exists('url_lang')) {
    function url_lang($url, $locale, $attributes = []){
        return LaravelLocalization::getLocalizedURL($locale, $url, $attributes);
    }
}

//removes language prefix from url
if (! function_exists('url_without_lang')) {
    function url_without_lang($url){
        return LaravelLocalization::getNonLocalizedURL($url);
    }
}

//returns asset() and ?v=last_modified_time, e.g. /app.css?v=123123
//useful for caching last modified version of an asset file
if (! function_exists('v_refresh')) {
    function asset_v_refresh($path){
        $a = asset($path);
        $pub = public_path($path);
        if(file_exists($pub)){
            $a .= '?v='.filemtime($pub);
        }
        return $a;
    }
}
