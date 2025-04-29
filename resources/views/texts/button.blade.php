@php
	if($button->type=='btn-primary') {
		$classes = "ml-3 inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2";
	} else if($button->type=='btn-secondary') {
		$classes = "ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2";
	} else if($button->type=='btn-danger') {
		$classes = "ml-3 inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2";
	} else if($button->type=='btn-outline-danger') {
		$classes = "ml-3 inline-flex items-center rounded-md border border-transparent text-red-600 px-4 py-2 text-sm font-medium bg-white shadow-sm hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 border border-red-700";
	}
@endphp
<a href="{{$button->link}}" type="button" class="{{$classes ?? ''}} {{$button->class}}" style="{{$button->style}}" {!! $button->extra !!}>
    {!! $button->text !!}
</a>