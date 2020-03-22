<div class="card text-white bg-dark mb-3 text-center">
  	<div class="card-body">
		@php $column = $input->column; @endphp
		@if(isset($input->resource) && isset($input->resource->object) && isset($input->resource->object->$column))
			<img src="{{getThumb($input->resource->object->$column, 'm')}}"><br /><br />
		@endif
		<button type="button" class="btn btn-secondary" onclick="executeFile('{{$id}}')">{{ __('Upload Image') }}</button>
		{!! Form::hidden($input->column, null) !!}
        {!! Form::file($input->column.'_new', ['id' => $id, 'style' => 'display:none;']) !!}
	</div>
</div>

@section('scripts-footer')
  	<script type="text/javascript">
		function executeFile(id) {
			console.log('#'+id);
		    $('#'+id).click();
		};
  	</script>
@endsection
