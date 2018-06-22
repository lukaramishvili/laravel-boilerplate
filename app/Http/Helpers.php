
if (! function_exists('lang')) {
    function lang(){
        return LaravelLocalization::getCurrentLocale();
    }
}
