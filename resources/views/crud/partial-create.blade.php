{!! \WeblaborMx\Front\Facades\Form::open(array('url' => $front->getBaseUrl(), 'files' => true)) !!}

    {!! \WeblaborMx\Front\Facades\Form::hidden('redirect_url') !!}
    @foreach($front->createPanels() as $panel)
        {!! $panel->formHtml() !!}
    @endforeach
    <div class="text-right mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Add') }} {{$front->label}}</button>
    </div>

{!! \WeblaborMx\Front\Facades\Form::close() !!}
