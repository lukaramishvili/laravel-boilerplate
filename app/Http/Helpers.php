<?php

if (! function_exists('lang')) {
    function lang(){
        return LaravelLocalization::getCurrentLocale();
    }
}

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
