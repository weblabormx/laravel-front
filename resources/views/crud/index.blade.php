@extends('front::layout')

@section('sidebar')

    @if($front->visibleFilters()->count() > 0)
        <div class="sidenav-header small font-weight-semibold mb-2">{{ __('FILTER :name', ['name' => strtoupper($front->plural_label)]) }}</div>
        {!! \WeblaborMx\Front\Facades\Form::open(['url' => request()->url(), 'method' => 'get']) !!} 
            <div class="card pt-3 sidenav-forms">
                {!! \WeblaborMx\Front\Facades\Form::hidden($front->getCurrentViewRequestName()) !!}
                @foreach($front->getFilters() as $filter)
                    {!! $filter->formHtml() !!}
                @endforeach
            </div>
            {!! \WeblaborMx\Front\Facades\Form::submit(__('Search'), ['class' => 'btn btn-secondary btn-sm btn-block']) !!}
        {!! \WeblaborMx\Front\Facades\Form::close() !!}
    @endif
    
@endsection
    
@section('content')
    
    @include('front::elements.breadcrumbs')

    <h4 class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div class="mb-4">{{$front->plural_label}}</div>
        <div class="d-print-none">
            @foreach($front->getIndexLinks() as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    </h4>

    @if($front->getLenses()->count() > 1)
        <div>
            <h4>Lenses</h4>
            @foreach($front->getLenses() as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    @endif

    @include ('front::components.cards', ['cards' => $front->cards()])
    @include ($front->getCurrentView())

@endsection

