@extends('front::layout')

@section('content')
    
    @include ('front::elements.errors')
    <!-- Content -->
    <div class="container-fluid flex-grow-1 container-p-y">
        @php $title_field = $front->title; @endphp
        @component('front::elements.breadcrumbs')
            @foreach($front->breadcrumbs() as $link => $title)
                <li class="breadcrumb-item"><a href="{{$link}}">{{$title}}</a></li>
            @endforeach
            <li class="breadcrumb-item"><a href="{{$front->base_url}}">{{$front->plural_label}}</a></li>
            <li class="breadcrumb-item"><a href="{{$front->base_url}}/{{$object->getKey()}}">{{ strip_tags($object->$title_field) }}</a></li>
            <li class="breadcrumb-item active">{{ $action->title }}</li>
        @endcomponent

        <h4 class="d-flex justify-content-between align-items-center w-100 font-weight-bold py-3 mb-4">
            <div>{{ $action->title }}</div>
            <div>
                @foreach($action->buttons() as $link => $button)
                    <a href="{{$link}}" class="btn btn-primary rounded-pill">{!! $button !!}</a>
                @endforeach
            </div>
        </h4>

        {!! Form::open(array('url' => request()->url())) !!}

            <div class="card">
                <hr class="m-0">
                <div class="card-body pb-2">
                    @foreach($action->fields() as $field)
                        {!! $field->formHtml() !!}
                    @endforeach
                </div>
            </div>
            <div class="text-right mt-3">
                <button type="submit" class="btn btn-primary">{{ $action->save_button }}</button>
            </div>

        {!! Form::close() !!}
    </div>

@stop

@section('footer')
    
    @if($action->getFieldsWithPanel()->count() <= 0)
        <script type="text/javascript">
            $('form').submit();
        </script>
    @endif
    
@stop