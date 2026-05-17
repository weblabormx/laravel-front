@php
    $front = $this->front();
    $result = $this->result();
@endphp

<section aria-label="{{ $front->plural_label }}" wire:key="front-resource-index-{{ md5($resource) }}">
    @include('front::crud.index-content')
</section>
