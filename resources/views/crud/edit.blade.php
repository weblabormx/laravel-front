@extends('front::layout')

@section('content')
    
    @include ('front::elements.errors')
    @include('front::elements.breadcrumbs')
    @php $title_field = $front->title; @endphp
    
    <h4 class="font-weight-bold py-3 mb-4">{{ __('Edit') }} @if($front->show_title) {{$object->$title_field}} @endif
    </h4>

    {!! Form::model($object, array('method' => 'put', 'url' => $front->base_url.'/'.$object->getKey())) !!}
    
        @foreach($front->editPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach
        <div class="text-right mt-3">
            @if( Auth::user()->can('delete', $object) )
                <a data-type="confirm" title="{{ __('Delete') }}" data-info="{{ __('Do you really want to remove this item?') }}" data-button-yes="{{ __('Yes') }}" data-button-no="{{ __('No') }}" data-action="{{url($front->base_url.'/'.$object->getKey())}}" data-redirection="{{url($front->base_url)}}" data-variables='{ "_method": "delete", "_token": "{{ csrf_token() }}" }' class="btn btn-danger" href="#"><i class="fa fa-times pr-2"></i> {{ __('Delete') }}</a>
            @endif
            <button type="submit" class="btn btn-primary"><i class="fa fa-save pr-2"></i> {{ __('Save Changes') }}</button>
        </div>
        
    {!! Form::close() !!}

@stop