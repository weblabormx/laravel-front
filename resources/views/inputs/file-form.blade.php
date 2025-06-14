{{ html()->file($input->column)->attributes($input->attributes) }}
@php $column = $input->column; $value = $input->resource?->object?->$column; @endphp
@if($value)
    <a href="{{ $value }}" target="_blank" class="text-xs text-base">{{ __('See Current file') }}</a>
@endif