@extends((isset($front) && isset($front->layout)) ? $front->layout : ((isset($page) && isset($page->layout)) ? $page->layout : config('front.default_layout')))
@push(config('front.scripts_stack'))
    @easyJsLibrary
@endpush
