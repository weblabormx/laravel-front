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
    <div class="container-fluid flex-grow-1 container-p-y">

        @component('front::elements.breadcrumbs')
            @foreach($page->breadcrumbs() as $link => $title)
                <li class="breadcrumb-item"><a href="{{$link}}">{{$title}}</a></li>
            @endforeach
            <li class="breadcrumb-item active">{{$page->title}}</li>
        @endcomponent
        

        <h4 class="d-flex justify-content-between align-items-center w-100 font-weight-bold py-3 mb-4">
            <div>{{$page->title}}</div>
            @foreach($page->index_links() as $link => $button)
                <a href="{{$link}}" class="btn btn-primary rounded-pill d-block">{!! $button !!}</a>
            @endforeach
        </h4>

        <div class="row">
            @foreach($page->allFields() as $field)
                {!! $field->html() !!}
            @endforeach
        </div>
    </div>

@endsection

