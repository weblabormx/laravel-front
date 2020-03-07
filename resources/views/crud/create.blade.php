@extends('front::layout')

@section('content')

    @include ('front::elements.errors')
    @include('front::elements.breadcrumbs')

    <h4 class="font-weight-bold py-3 mb-4">{{ __('Create') }} {{$front->label}}</h4>

    {!! Form::open(array('url' => $front->base_url, 'files' => true)) !!}
    
        {!! Form::hidden('redirect_url') !!}
        @foreach($front->createPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach
        <div class="text-right mt-3">
            <button type="submit" class="btn btn-primary">{{ __('Add') }} {{$front->label}}</button>
        </div>

    {!! Form::close() !!}

@stop
