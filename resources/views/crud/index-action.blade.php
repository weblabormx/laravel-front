@extends('front::layout')

@section('content')

    @component('components.breadcrumbs')
        @foreach($sportable->breadcrumbs() as $link => $title)
            <li><a href="{{$link}}">{{$title}}</a></li>
        @endforeach
        <li><a href="{{$sportable->base_url}}">{{$sportable->plural_title}}</a></li>
        <li><a href="{{$sportable->base_url}}/{{$front->data['sport']->sports}}">{{$front->data['sport']->league_desc}}</a></li>
        <li><a href="#">{{$action->title}}</a></li>
    @endcomponent

    {!! Form::open(array('url' => request()->url(), 'class' => 'edit-section')) !!}

        <div class="show-title">
            <div class="buttons">
                @foreach($action->buttons() as $link => $button)
                    <a href="{{$link}}" class="add-btn">{!! $button !!}</a>
                @endforeach
                <button type="submit" class="add-btn">
                    <i class="fa fa-save fa-lg"></i> Save
                </button>
            </div>
            <h1>{{$action->title}}</h1>
        </div>
        
        @include ('layout.errors')        
        @include ('front::elements.partial-form', ['fields' => $action->fields(), 'is_action' => true])

    {!! Form::close() !!}

@stop

@section('footer')
    
    @if($action->getFieldsWithPanel()->count() <= 0)
        <script type="text/javascript">
            $('form').submit();
        </script>
    @endif
    
@stop