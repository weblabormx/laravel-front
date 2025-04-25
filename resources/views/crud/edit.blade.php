@extends('front::layout')

@section('content')
    
    @include('front::elements.breadcrumbs')
    @include ('front::elements.errors')
    
    <h4 class="font-weight-bold py-3 mb-4">{{ __('Edit') }} {{$front->getTitle($object)}}</h4>

    {!! \WeblaborMx\Front\Facades\Form::model($object, array('method' => 'put', 'url' => $front->getBaseUrl().'/'.$object->getKey(), 'files' => true)) !!}

        {!! \WeblaborMx\Front\Facades\Form::hidden('redirect_url') !!}
        @foreach($front->editPanels() as $panel)
            {!! $panel->formHtml() !!}
        @endforeach

        <div class="text-right mt-3">
            @if( $front->canRemove($object) )
                {!! getButtonByName('delete', $front, $object)->form() !!}
            @endif
            <button type="submit" class="btn btn-primary"><i class="fa fa-save pr-2"></i> {{ __('Save Changes') }}</button>
        </div>
        
    {!! \WeblaborMx\Front\Facades\Form::close() !!}

@stop
