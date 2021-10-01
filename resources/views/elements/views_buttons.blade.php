@foreach($views as $view)
    <a href="{{$view['url']}}" class="{{$view['icon']}} @if($view['is_active']) active @endif" title="{{$view['title']}}"></a>
@endforeach