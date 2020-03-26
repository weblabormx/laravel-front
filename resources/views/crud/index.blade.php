@extends('front::layout')

@section('sidebar')
    @if(count($front->filters())>0)
        <div class="sidenav-header small font-weight-semibold mb-2">{{ __('FILTER :name', ['name' => strtoupper($front->plural_label)]) }}</div>
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

    <h4 class="justify-content-between align-items-center mb-4">
        <div class="float-right">
            @foreach($front->all_index_links() as $link => $button)
                <a href="{{$link}}" class="btn btn-primary rounded-pill">{!! $button !!}</a>
            @endforeach
        </div>
        <div>{{$front->plural_label}}</div>
    </h4>

    @include ('front::components.cards', ['cards' => $front->cards()])
    @include ('front::crud.partial-index', ['object' => $objects])

@endsection

