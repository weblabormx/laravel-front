@extends('front::layout')

@section('sidebar')

    @if(count($front->filters())>0)
        <div class="sidenav-inner py-1 mb-4">
            <div class="sidenav-header small font-weight-semibold">{{ __('FILTER') }} {{strtoupper($front->plural_label)}}</div>
            <!-- Dashboards -->
            {!! Form::open(['url' => request()->url(), 'method' => 'get']) !!}
                {!! Form::hidden('search') !!}
                @foreach($front->getFilters() as $filter)
                    <div class="sidenav-forms">
                        {!! $filter->formHtml() !!}
                    </div>
                @endforeach
                <div class="sidenav-forms mt-2">
                    {!! Form::submit('Search') !!}
                </div>
            {!! Form::close() !!}
        </div>
    @endif

@endsection
    
@section('content')
    
    <!-- Content -->
    <div class="container-fluid container">
        @include('front::elements.breadcrumbs')

        <h4 class="d-flex justify-content-between align-items-center w-100 font-weight-bold py-3 mb-4">
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

    </div>
   

@endsection

