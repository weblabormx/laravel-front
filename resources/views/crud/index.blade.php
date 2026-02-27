@extends('front::layout')

@section('content')

    <!-- This example requires Tailwind CSS v2.0+ -->
    @include('front::elements.breadcrumbs')

    <div class="mt-2 md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:tracking-tight">{{$front->plural_label}}</h2>
        </div>
        <div class="mt-4 flex flex-wrap gap-2 shrink-0 md:mt-0 md:ml-4">
            @foreach($front->getIndexLinks() as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    </div>
    @if($front->hasFilters())
        <div x-data="{ filterShow: false }">
            <div class="mt-6 font-bold">
                {{ __('FILTER RESULTS', ['name' => strtoupper($front->plural_label)]) }}
                <span class="rounded bg-gray-200 text-gray-700 px-2 py-0.5 text-xs font-semibold mx-1">{{ collect($front->getFilters())->filter(fn($f) => request()->filled($f->slug))->count() }}</span>
                <x-icon name="funnel" class="inline w-5 h-5 cursor-pointer text-gray-500 hover:text-gray-700" x-on:click="filterShow = !filterShow" />
            </div>
            {{ html()->formWithDefaults(request()->all(), 'GET', request()->url())->open() }}
                <div class="mt-2 mb-4 flex flex-col sm:flex-row flex-wrap gap-x-6 gap-y-2 bg-slate-50 p-4 border border-gray-200 rounded-lg" x-show="filterShow" x-transition>
                    {{ html()->hidden($front->getCurrentViewRequestName()) }}
                    @foreach($front->getFilterInputs() as $filter)
                        {!! $filter->formHtml() !!}
                    @endforeach
                    <div>
                        {{ html()->submit(__('Search'))->class('bg-primary-600 text-white px-4 rounded md:mt-6 py-2 cursor-pointer') }}
                    </div>
                </div>
            {{ html()->closeFormWithDefaults() }}
        </div>
    @endif

    @if($front->getLenses()->count() > 1)
        <div class="flex flex-wrap gap-3 items-center mt-6">
            <h4 class="text-lg font-medium">Lenses</h4>
            @foreach($front->getLenses() as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    @endif

    @include ('front::components.cards', ['cards' => $front->cards()])
    @include ($front->getCurrentView())

@endsection

