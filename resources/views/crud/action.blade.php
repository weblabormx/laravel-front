@extends('front::layout')

@section('content')
    
    @include('front::elements.breadcrumbs', ['data' => ['action' => $action]])
    @include('front::elements.errors')

    <div class="mt-2 md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ $action->title }}</h2>
        </div>
        <div class="mt-4 flex flex-shrink-0 md:mt-0 md:ml-4">
            @foreach($action->buttons() as $link => $button)
                <a href="{{$link}}" class="ml-3 inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{!! $button !!}</a>
            @endforeach
        </div>
    </div>

    {{ html()->form('POST', request()->url())->acceptsFiles()->open() }}

        @foreach($action->createPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach

        @if($action->hasHandle())
            <div class="text-right mt-3">
                <button type="submit" class="ml-3 inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ $action->save_button }}</button>
            </div>
        @endif

    {{ html()->form()->close() }}
    
@stop
