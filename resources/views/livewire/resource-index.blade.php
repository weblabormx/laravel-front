@php
    $front = $this->front();
    $result = $this->result();
    $frontIndexComponent = $this;
@endphp

<section aria-label="{{ $front->plural_label }}" wire:key="front-resource-index-{{ md5($resource) }}">
    @include('front::livewire.column-preferences')
    @include('front::crud.index-content')
</section>
