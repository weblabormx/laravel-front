@if($value && $value != '--')
    <a href="{{$value}}" target="_blank">{{ __('See file') }}</a>
@else
    --
@endif