@extends('front::layout')

@section('content')

    @include ('front::elements.errors')
    @include('front::elements.breadcrumbs')

    <h4 class="font-weight-bold py-3 mb-4">{{ __('Create') }} {{$front->label}}</h4>

    @include ('front::crud.partial-create', ['front' => $front])

@stop