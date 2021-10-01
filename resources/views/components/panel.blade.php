<div class="card mb-4 {{$panel->class}}" style="{{$panel->style_width()}}">
    @if(strlen($panel->title) > 0)
        <div class="card-header">
            <b>{{$panel->title}}</b>
            @if(count($panel->actions)>0)
                @foreach($panel->actions as $action)
                    <a href="{{$panel->resource->base_url}}/{{$object->getKey()}}/action/{{$action->slug}}" class="btn btn-outline-dark btn-sm mx-2">{!! $action->button_text !!}</a>
                @endforeach
            @endif
            @if(count($panel->links)>0)
                @foreach($panel->links as $link => $title)
                    <a href="{{$link}}" class="btn btn-outline-dark btn-sm mx-2">{!! $title !!}</a>
                @endforeach
            @endif
        </div>
    @endif
    <div class="card-body">
        <div class="row">
        	@foreach($panel->fields() as $field)
                {!! $field->showHtml($object) !!}
            @endforeach
        </div>
    </div>
</div>
