@extends('front::layout')

@section('sidebar')
    @if(count($front->filters())>0)
        <div class="sidenav-header small font-weight-semibold mb-2">{{ __('FILTER') }} {{strtoupper($front->plural_label)}}</div>
        {!! Form::open(['url' => request()->url(), 'method' => 'get']) !!}
            <div class="card pt-3">
                {!! Form::hidden('search') !!}
                @foreach($front->getFilters() as $filter)
                    <div class="sidenav-forms">
                        {!! $filter->formHtml() !!}
                    </div>
                @endforeach
            </div>
            {!! Form::submit(__('Search'), ['class' => 'btn btn-secondary btn-sm btn-block']) !!}
        {!! Form::close() !!}
    @endif
@endsection
    
@section('content')
    
    @include('front::elements.breadcrumbs')

    <h4 class="d-flex justify-content-between align-items-center mb-4">
        <div>{{$front->plural_label}}</div>
        @if($front->show_create_button_on_index && Auth::user()->can('create', $front->getModel()))
            <a href="{{$front->base_url}}/create" class="btn btn-primary rounded-pill d-block"><span class="fa fa-plus"></span>&nbsp; {{ __('Create') }} {{$front->label}}</a>
        @endif
        @include('front::elements.index-actions')
        @foreach($front->index_links() as $link => $button)
            <a href="{{$link}}" class="btn btn-primary rounded-pill d-block">{!! $button !!}</a>
        @endforeach
    </h4>

    @if(count($front->cards())>0)
        @include ('front::components.cards', ['cards' => $front->cards()])
    @endif

    @include ('front::crud.partial-index', ['object' => $objects])

@endsection

