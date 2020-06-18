{!! Form::open(['url' => request()->url(), 'method' => 'get']) !!}
    @foreach($front->getFilters() as $filter)
        {!! $filter->formHtml() !!}
    @endforeach
    {!! Form::submit('Search') !!}
{!! Form::close() !!}
