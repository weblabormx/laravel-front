@extends('front::layout')

@section('content')
    
    @include ('front::elements.errors')

    <!-- Content -->
    <div class="container-fluid flex-grow-1 container-p-y">
        @include('front::elements.breadcrumbs')

        <h4 class="font-weight-bold py-3 mb-4">Create {{$front->label}}</h4>

        {!! Form::open(array('url' => $front->base_url)) !!}
            {!! Form::hidden('redirect_url') !!}
            @foreach($front->createPanels() as $panel)
                {!! $panel->formHtml() !!}
            @endforeach
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-primary">Add {{$front->label}}</button>
            </div>

        {!! Form::close() !!}
    </div>

@stop