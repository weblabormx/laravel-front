<div class="card text-white bg-dark mb-3 text-center">
  	<div class="card-body">
		<button type="button" class="btn btn-secondary" onclick="executeFileMultiple('{{$id}}')">{{ __('Upload Images') }}</button>
        {!! Form::file($input->column.'[]', ['id' => $id, 'style' => 'display:none;', 'multiple' => 'multiple']) !!}
	</div>
</div>

@pushonce('scripts-footer')
  	<script>
		function executeFileMultiple(id) {
		    $('#'+id).click();
		};
  	</script>
@endpushonce
