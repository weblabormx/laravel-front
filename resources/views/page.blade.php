@extends('front::layout')

@section('styles')
    @if (!is_null($page->style()))
        <style type="text/css">
            {!! $page->style() !!}
        </style>
    @endif
@endsection

@section('content')

    <!-- Content -->
    @component('front::elements.breadcrumbs')
        @foreach ($page->breadcrumbs() as $link => $title)
            <li class="relative before:absolute before:top-0 before:-translate-x-full before:left-0 before:content-['/'] pl-2">
                <a href="{{ $link }}">{{ $title }}</a>
            </li>
        @endforeach
        <li class="relative before:absolute before:top-0 before:-translate-x-full before:left-0 before:content-['/'] pl-2">
            <span class="text-sm font-medium">
                {{ $page->title }}
            </span>
        </li>
    @endcomponent

    @if ($page->has_big_card)
        <div class="p-7 bg-white rounded-md shadow-md">
            <div class="flex flex-wrap gap-10 justify-between pb-7">
                <h1 class="text-xl font-medium">{{ $page->title }}</>
                    @foreach ($page->getLinks() as $button)
                        {!! $button->form() !!}
                    @endforeach
            </div>
            <div class="p-5 border-t">
                <div>
                    @foreach ($page->allFields() as $field)
                        {!! $field->html() !!}
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div>
            @foreach ($page->allFields() as $field)
                {!! $field->html() !!}
            @endforeach
        </div>
    @endif

@endsection
