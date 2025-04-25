@extends('front::layout')

@section('content')
    
    @include('front::elements.breadcrumbs', ['data' => ['action' => $action]])
    @include('front::elements.errors')

    <h4 class="d-flex align-items-center font-weight-bold py-3 mb-4">
        <div>{{ $action->title }}</div>
        <div>
            @foreach($action->buttons() as $link => $button)
                <a href="{{$link}}" class="btn btn-primary rounded-pill">{!! $button !!}</a>
            @endforeach
        </div>
    </h4>

    {!! \WeblaborMx\Front\Facades\Form::open(array('url' => request()->url(), 'files' => true)) !!}

        @foreach($action->createPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach

        @if($action->hasHandle())
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-primary">{{ $action->save_button }}</button>
            </div>
        @endif

    {!! \WeblaborMx\Front\Facades\Form::close() !!}
    
@stop
