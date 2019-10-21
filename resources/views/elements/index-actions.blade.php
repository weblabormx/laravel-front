@if(count($front->indexActions())>0)
    @foreach($front->indexActions() as $action)
        <a href="{{$sportable->base_url}}/{{$front->data['sport']->sports}}/action/{{$action->slug}}" class="btn btn-primary rounded-pill d-block">{!! $action->button_text !!}</a>
    @endforeach
@endif