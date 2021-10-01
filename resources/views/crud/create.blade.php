@extends('front::layout')

@section('content')

    @include('front::elements.breadcrumbs')
    @include ('front::elements.errors')

    <h4 class="font-weight-bold py-3 mb-4">{{ __('Create') }} {{$front->label}}</h4>

    @include ('front::crud.partial-create', ['front' => $front])

@stop