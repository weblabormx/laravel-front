<div class="col-span-{{$input->bootstrap_width()}}">
	<label class="block text-sm font-medium text-gray-700">{{ $input->title }}</label>
    {!! $input->form() !!}
    @if(isset($input->help))
	    <small class="text-gray-400 block text-xs mt-2">{!! $input->help !!}</small>
	@endif
</div>
