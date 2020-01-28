@if(count($front->getActions())>0)
    @foreach($front->getActions() as $action)
    	@if($action->show_button)
        	<a href="{{$front->base_url}}/{{$object->getKey()}}/action/{{$action->slug}}" class="btn btn-primary rounded-pill" style="{{$action->getStyle()}}">{!! $action->button_text !!}</a>
        @endif
    @endforeach
@endif