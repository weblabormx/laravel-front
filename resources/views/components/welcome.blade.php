<h4 class="media align-items-center font-weight-bold py-3 mb-4 {{$component->class}}">
    <img src="{{Auth::user()->photo_url}}" alt="" class="ui-w-50 rounded-circle">
    <div class="media-body ml-3">
        Welcome back, {{Auth::user()->name}}!
        <div class="text-muted text-tiny mt-1"><small class="font-weight-normal">Today is {{now()->format('l, F j, Y')}}</small></div>
    </div>
</h4>
