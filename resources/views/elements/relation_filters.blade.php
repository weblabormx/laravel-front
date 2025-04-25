{!! \WeblaborMx\Front\Facades\Form::open(['url' => request()->url(), 'method' => 'get']) !!}
    @foreach($front->getFilters() as $filter)
        {!! $filter->formHtml() !!}
    @endforeach
    {!! \WeblaborMx\Front\Facades\Form::submit('Search') !!}
{!! \WeblaborMx\Front\Facades\Form::close() !!}
