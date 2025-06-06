{{ html()->form('GET', request()->url())->open() }}
    @foreach ($front->getFilters() as $filter)
        {!! $filter->formHtml() !!}
    @endforeach

    {{ html()->submit(__('Search')) }}
{{ html()->form()->close() }}
