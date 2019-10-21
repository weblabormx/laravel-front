@extends('front::layout')

@section('content')
    
    <!-- Content -->
    <div class="container-fluid flex-grow-1 container-p-y">
        @include('front::elements.breadcrumbs')

        <h4 class="d-flex justify-content-between align-items-center w-100 font-weight-bold py-3 mb-4">
            @if($front->show_title)
                @php $view_title_field = $front->view_title; @endphp
                <div>{{$object->$view_title_field}}</div>
            @else
                <div>{{$front->plural_label}}</div>
            @endif
            <div>
                @include('front::elements.actions')
                @foreach($front->links() as $link => $text)
                    <a href="{{ $link }}" class="btn btn-primary rounded-pill">{!! $text !!}</a>
                @endforeach
                @if(Auth::user()->can('update', $object))
                    <a href="{{$front->base_url}}/{{$object->getKey()}}/edit{{str_replace(request()->url(), '', request()->fullUrl())}}" class="btn btn-primary rounded-pill"><span class="fa fa-edit"></span>&nbsp; Edit</a>
                @endif
                @if( Auth::user()->can('delete', $object) )
                    <a data-type="confirm" title="Do you really want to remove this item?" data-info="Do you really want to remove this item?" data-button-yes="Yes" data-button-no="No" data-action="{{url($front->base_url.'/'.$object->getKey())}}" data-redirection="{{url($front->base_url)}}" data-variables='{ "_method": "delete", "_token": "{{ csrf_token() }}" }' class="btn btn-danger rounded-pill" href="#"><i class="fa fa-times pr-2"></i> Delete</a>
                @endif
            </div>
        </h4>

        @foreach($front->showPanels() as $panel)
            {!! $panel->showHtml($object) !!}
        @endforeach

        @foreach($front->showRelations() as $relation)
            <div class="pb-4">
                <h4 class="d-flex justify-content-between align-items-center w-100 font-weight-bold">
                    <div>{{$relation->title}}</div>
                    <div>
                        @if(count($relation->actions)>0)
                            @foreach($relation->actions as $action)
                                <a href="{{$front->base_url}}/{{$object->getKey()}}/action/{{$action->slug}}" class="btn btn-primary rounded-pill">{!! $action->button_text !!}</a>
                            @endforeach
                        @endif
                        @if(count($relation->links)>0)
                            @foreach($relation->links as $link => $title)
                                <a href="{{$link}}" class="btn btn-primary rounded-pill">{!! $title !!}</a>
                            @endforeach
                        @endif
                        @if( Auth::user()->can('create', $relation->front->getModel()) && isset($relation->create_link))
                            <a href="{{$relation->create_link}}" class="btn btn-primary rounded-pill"><span class="ion ion-md-add"></span> Add {{$relation->front->label}}</a>
                        @endif
                    </div>
                </h4>
                {!! $relation->getValue($object) !!}
            </div>
        @endforeach
    </div>
   
@endsection


