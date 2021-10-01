<h4 class="media align-items-center font-weight-bold py-3 mb-4 {{$component->class}}">
    <img src="{{Auth::user()->photo_url}}" alt="" class="ui-w-50 rounded-circle">
    <div class="media-body ml-3">
        {{ __('Welcome back') }}, {{Auth::user()->name}}!
        <div class="text-muted text-tiny mt-1"><small class="font-weight-normal">{{ __('Today is') }} {{now()->format($component->date_format)}}</small></div>
    </div>
</h4>
