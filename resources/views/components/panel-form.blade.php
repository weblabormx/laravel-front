<div class="card" style="margin-bottom: 20px;">
    @if(strlen($panel->title) > 0)
        <h6 class="card-header">{{$panel->title}}</h6>
    @endif
    <hr class="m-0">
    <div class="card-body pb-2">
    	<div class="row">
	        @foreach($panel->fields() as $field)
	            {!! $field->formHtml() !!}
	        @endforeach
        </div>
    </div>
</div>
