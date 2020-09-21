<div class="card text-white bg-dark mb-3 text-center">
  	<div class="card-body">
		<button type="button" class="btn btn-secondary" onclick="executeFile('{{$id}}')">{{ __('Upload Images') }}</button>
        {!! Form::file($input->column.'[]', ['id' => $id, 'style' => 'display:none;', 'multiple' => 'multiple']) !!}
	</div>
</div>

@pushonce('scripts-footer')
  	<script type="text/javascript">
		function executeFile(id) {
		    $('#'+id).click();
		};
  	</script>
@endpushonce
