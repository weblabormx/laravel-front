{{ html()->form('POST', $front->getBaseUrl())->acceptsFiles()->open() }}

    {{ html()->hidden('redirect_url') }}
    @foreach ($front->createPanels() as $panel)
        {!! $panel->formHtml() !!}
    @endforeach
    <div class="text-right mt-3">
        <button type="submit" class="ml-3 inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ __('Add') }} {{$front->label}}
        </button>
    </div>

{{ html()->form()->close() }}
