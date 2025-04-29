<div class="mt-1 flex rounded-md shadow-sm">
	@isset($group->before)
  		@foreach($group->before as $text)
  			<span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">{{ $text }}</span>
    	@endforeach
  	@endisset
  	{!! $group->input !!}
  	@isset($group->after)
  		@foreach($group->after as $text)
  			<span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">{{ $text }}</span>
    	@endforeach
  	@endisset
</div>
