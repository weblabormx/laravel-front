{{ html()->form('POST', $front->getBaseUrl())->acceptsFiles()->open() }}

{{ html()->hidden('redirect_url') }}
@foreach ($front->createPanels() as $panel)
    {!! $panel->formHtml() !!}
@endforeach
<div class="text-right mt-3">
    <button type="submit" class="btn btn-primary">{{ __('Add') }} {{ $front->label }}</button>
</div>

{{ html()->form()->close() }}
