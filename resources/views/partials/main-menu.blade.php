
Languages:
@foreach(LaravelLocalization::getLocalesOrder() as $lang => $lang_details)
  <a href="{{ url_lang('/'.$lang, $lang) }}" @if($lang == lang()) style="text-decoration: underline;" @endif>
    {{ $lang.' ('.$lang_details['native'].', '.$lang_details['name'].')' }}
  </a>
@endforeach
