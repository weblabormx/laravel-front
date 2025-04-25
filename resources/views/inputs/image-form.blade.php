<div class="card text-white bg-dark mb-3 text-center">
  	<div class="card-body" id="{{$id}}">
		@php $column = $input->column; @endphp
		@if(isset($input->resource) && isset($input->resource->object) && isset($input->resource->object->$column))
			<img src="{{getThumb($input->resource->object->$column, $input->view_size)}}" class="mw-100"><br /><br />
		@elseif(isset($input->value))
			<img src="{{$input->value}}" class="mw-100"><br /><br />
		@endif
		<p class="file-name"></p>
		<button type="button" class="btn btn-secondary" onclick="executeFile('{{$id}}')">{{ __('Upload Image') }}</button>
		{!! \WeblaborMx\Front\Facades\Form::hidden($input->column, $input->value) !!}
        {!! \WeblaborMx\Front\Facades\Form::file($input->column.'_new', ['style' => 'display:none;']) !!}
	</div>
</div>

@pushonce('scripts-footer')
  	<script>
		function executeFile(id) {
		    $('#'+id+' > input[type="file"]').click();
		};
  	</script>
@endpushonce
@push('scripts-footer')
  	<script>
		$('#{{$id}} > input[type="file"]').change(function(e){
            var fileName = e.target.files[0].name;
            $('#{{$id}} .file-name').html(fileName);
        });
  	</script>
@endpush
