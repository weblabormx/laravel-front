<div class="input-group mb-3">
	@isset($group->before)
	  	<div class="input-group-prepend">
	  		@foreach($group->before as $text)
	    		<span class="input-group-text">{{ $text }}</span>
	    	@endforeach
	  	</div>
  	@endisset
  	{!! $group->input !!}
  	@isset($group->after)
	  	<div class="input-group-append">
	  		@foreach($group->after as $text)
	    		<span class="input-group-text">{{ $text }}</span>
	    	@endforeach
	  	</div>
  	@endisset
</div>