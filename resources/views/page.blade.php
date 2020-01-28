@extends('front::layout')

@section('styles')

    @if(!is_null($page->style()))
        <style type="text/css">
            {!! $page->style() !!}
        </style>
    @endif

@endsection

@section('content')

    <!-- Content -->
    <div class="container-fluid container">
        @component('front::elements.breadcrumbs')
            @foreach($page->breadcrumbs() as $link => $title)
                <li class="breadcrumb-item"><a href="{{$link}}">{{$title}}</a></li>
            @endforeach
            <li class="breadcrumb-item active">{{$page->title}}</li>
        @endcomponent

        <div class="card card-default">
            <div class="card-header">
                {{$page->title}}
            </div>

            <div class="card-body">
                @foreach($page->allFields() as $field)
                    {!! $field->html() !!}
                @endforeach
            </div>
        </div>
    </div>

@endsection

