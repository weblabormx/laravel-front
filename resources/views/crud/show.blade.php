@extends('front::layout')

@section('content')
    @include('front::elements.breadcrumbs')

    <div class="mt-2 md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                {!! $front->getTitle($object) !!}</h2>
        </div>
        <div class="flex flex-shrink-0 mt-4 md:mt-0 md:ml-4">
            @foreach ($front->getLinks($object) as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    </div>

    <div class="mt-12">
        @foreach ($front->showPanels() as $panel)
            {!! $panel->showHtml($object) !!}
        @endforeach

        @php
            $porcentage = 0;
        @endphp

        @foreach ($front->showRelations() as $key => $relation)
            @php $porcentage += $relation->width_porcentage(); @endphp
            <div class="relation mt-8" style="{{ $relation->style_width() }}">
                <div>
                    <h4 class="flex items-center">
                        <div class="text-lg font-medium">{{ $relation->title }}</div>
                        <div>
                            @foreach ($relation->getLinks($object, $key, $front) as $button)
                                {!! $button->form() !!}
                            @endforeach
                        </div>
                    </h4>
                    {!! $relation->getValue($object) !!}
                </div>
            </div>
            @if ($porcentage >= 100)
                @php $porcentage = 0; @endphp
                <div style="clear:both;"></div>
            @endif
        @endforeach
    </div>

    @if (method_exists($object, 'getActivitylogOptions'))
        @include('front.timeline', ['object' => $object])
    @endif
@endsection
