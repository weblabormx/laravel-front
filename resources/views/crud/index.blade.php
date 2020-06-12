@extends('front::layout')

@section('sidebar')

    @if(count($front->filters())>0)
        <div class="sidenav-header small font-weight-semibold mb-2">{{ __('FILTER :name', ['name' => strtoupper($front->plural_label)]) }}</div>
        {!! Form::open(['url' => request()->url(), 'method' => 'get']) !!} 
            <div class="card pt-3 sidenav-forms">
                {!! Form::hidden('search') !!}
                @foreach($front->getFilters() as $filter)
                    {!! $filter->formHtml() !!}
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
        <div>
            @foreach($front->getIndexLinks() as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    </h4>

    @include ('front::components.cards', ['cards' => $front->cards()])
    @include ($front->getCurrentView())

@endsection

