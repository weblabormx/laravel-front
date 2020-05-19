@extends('front::layout')

@section('content')
    
    @include('front::elements.breadcrumbs')
    @include ('front::elements.errors')
    
    <h4 class="font-weight-bold py-3 mb-4">{{ __('Edit') }} {{$front->getTitle($object)}}</h4>

    {!! Form::model($object, array('method' => 'put', 'url' => $front->getBaseUrl().'/'.$object->getKey(), 'files' => true)) !!}
    
        @foreach($front->editPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach

        <div class="text-right mt-3">
            @if( Auth::user()->can('delete', $object) )
                <a data-type="confirm" title="{{ __('Delete') }}" data-info="{{ __('Do you really want to remove this item?') }}" data-button-yes="{{ __('Yes') }}" data-button-no="{{ __('No') }}" data-action="{{url($front->getBaseUrl().'/'.$object->getKey())}}" data-redirection="{{url($front->getBaseUrl())}}" data-variables='{ "_method": "delete", "_token": "{{ csrf_token() }}" }' class="btn btn-danger" href="#"><i class="fa fa-times pr-2"></i> {{ __('Delete') }}</a>
            @endif
            <button type="submit" class="btn btn-primary"><i class="fa fa-save pr-2"></i> {{ __('Save Changes') }}</button>
        </div>
        
    {!! Form::close() !!}

@stop
