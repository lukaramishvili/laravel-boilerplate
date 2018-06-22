<!DOCTYPE html>
<html lang="{{ lang() }}">
  <head>
    <title>Welcome</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- add a post var _token containing this token when doing AJAX calls -->
    <meta name="csrf-token" content="{{{ Session::token() }}}">
    <link rel="shortcut icon" href="{{ url('/favicon.ico') }}">

    <meta property="fb:app_id" content="{{ Config::get('services.facebook.client_id') }}" />


    <meta property="og:site_name" content="{{ Config::get('app.name') }}" />
    @section('social-meta-tags')
      <meta property="og:title" content="{{ Config::get('app.name') }}" />
      <meta property="og:url" content="{{ url()->full() }}" />
      <meta property="og:type" content="website" />
      <meta property="og:description" content="{{ trans('app.meta_desc') }}" />
      <meta property="og:image" content="{{ asset('img/share-image.jpg') }}" />

      <meta itemprop="name" content="{{ Config::get('app.name') }}">
      <meta itemprop="description" content="{{ trans('app.meta_desc') }}">
      <meta itemprop="image" content="{{ asset('img/share-image.jpg') }}">
    @show

    <!-- Global CSS -->
    <!-- Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('/css/vendor.css') }}">
    <link rel="stylesheet" href="{{ asset_v_refresh('/css/app.css') }}">
    @yield('external_style')
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    <!-- google analytics -->
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', '{{ Config::get('services.ganalytics.account_id') }}', 'auto');
        ga('send', 'pageview');
    </script>

    <style type="text/css">

        @section('style-head')
        @show

    </style>

    @section('head-extras')
    @show

    <!-- <script src='https://www.google.com/recaptcha/api.js' async defer></script> -->

  </head>
  <body class="
@if(\App\Helper::isMobile()) platform-mobile @else platform-desktop @endif
@if(Auth::check()) authed @endif
lang-{{ lang() }}
@if(isset($currentpage)) {{ $currentpage }}-page @endif
 ">
  <script>
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '{{ Config::get('services.facebook.client_id') }}',
            cookie     : true,
            xfbml      : true,
            version    : 'v2.8'
        });
        FB.AppEvents.logPageView();
    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function checkLoginState() {
        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
    }

    function statusChangeCallback(response){
        console.log(response);
    }
  </script>
  <script>
    window.lang = '{{ lang() }}';
    window.authed_user_id = {{ Auth::check() ? Auth::user()->id : 0 }};
    //
    window.transList = {
@foreach([
      "app.meta_desc",
] as $inline_trans_key)
"{{ $inline_trans_key }}" : "{!! str_replace('"','\\"', str_replace('\\','\\\\', trans($inline_trans_key))) !!}",
@endforeach
    };
    window.trans = function(msg, params){
      var ret = window.transList[msg] ? window.transList[msg] : msg;
      if(typeof(params) === 'object'){
          Object.getOwnPropertyNames(params).forEach(function(pname){
              ret = ret.replace(new RegExp(":"+pname,"gi"),params[pname]);
          });
      }
      return ret;
    };
    //
    window.timezoneOffset = {{ \App\Helper::getUTCOffset() }};//1 means UTC+1
    //
    window.valmsg_please_fill_correctly = '{!! trans('register.valmsg_please_fill_correctly') !!}';
    window.valmsg_please_enter_correct_phone_number = '{!! trans('register.valmsg_please_enter_correct_phone_number') !!}';
    window.valmsg_mistake_in_password = '{!! trans('register.valmsg_mistake_in_password') !!}';
    window.valmsg_password_rules_text = '{!! trans('register.valmsg_password_rules_text') !!}';

    </script>



    <script src="{{ asset('/js/vendor.js') }}"></script>
    @section('bottom-extras')
    @show
    <script src="{{ asset_v_refresh('/js/main.js') }}"></script>
    @if(Auth::check() && Auth::user()->type == "admin")
    <script src="{{ asset_v_refresh('/js/admin.js') }}"></script>
    @endif
    <script type="text/javascript">
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      @section('script-bottom')
      @show

    </script>

  </body>
</html>
