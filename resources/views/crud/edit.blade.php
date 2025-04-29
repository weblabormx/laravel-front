@extends('front::layout')

@section('content')
    
    @include('front::elements.breadcrumbs')
    @include ('front::elements.errors')
    
    {{ html()->modelForm($object, 'PUT', $front->getBaseUrl() . '/' . $object->getKey())->acceptsFiles()->open() }}

        <div class="mt-2 md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ __('Edit') }} {{$front->getTitle($object)}}</h2>
            </div>
            <div class="mt-4 flex flex-shrink-0 md:mt-0 md:ml-4">
                @if( $front->canRemove($object) )
                    {!! \Front::buttons()->getByName('delete', $front, $object)->form() !!}
                @endif
                <button type="submit" class="ml-3 inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" />
                    </svg>
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>

        {{ html()->hidden('redirect_url') }}
        @foreach($front->editPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach
        
    {{ html()->closeModelForm() }}

@stop
