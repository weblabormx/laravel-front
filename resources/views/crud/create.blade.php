@extends('front::layout')

@section('content')

    @include('front::elements.breadcrumbs')
    @include ('front::elements.errors')

    <h4 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ __('Create') }} {{$front->label}}</h4>

    @include ('front::crud.partial-create', ['front' => $front])

@stop