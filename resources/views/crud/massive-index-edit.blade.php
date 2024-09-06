@extends('front::layout')

@section('sidebar')

    @if (count($front->filters()) > 0)
        <div class="sidenav-header small font-weight-semibold mb-2 text-uppercase">{{ __('Options') }}</div>

        {{ html()->form('GET', request()->url())->open() }}

        <div class="card pt-3 sidenav-forms">
            @foreach ($front->getMassiveForms() as $form)
                {!! $form->formHtml() !!}
            @endforeach
        </div>

        {{ html()->submit(__('Search'))->class('btn btn-secondary btn-sm btn-block') }}

        {{ html()->form()->close() }}
    @endif

@endsection

@section('content')
    @include('front::elements.breadcrumbs', ['data' => ['massive' => $front]])
    @include ('front::elements.errors')


    <h4 class="font-weight-bold py-3">{{ __('Edit') }} {{ $front->plural_label }}</h4>

    {{ html()->form('POST', request()->url())->acceptsFiles()->open() }}

    <div class="table-responsive">
        <table class="table table-striped bg-white">
            <thead class="thead-dark">
                <tr>
                    @foreach ($front->getTableHeadings() as $title)
                        <th>{{ $title }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $object)
                    <tr>
                        @foreach ($front->getTableValues($object) as $value)
                            <td>{!! $value !!}</td>
                        @endforeach
                    </tr>
                @endforeach
                @foreach ($front->getExtraTableValues() as $row)
                    <tr>
                        @foreach ($row as $value)
                            <td>{!! $value !!}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @foreach (request()->except('rows') as $key => $value)
        {{ html()->hidden($key) }}
    @endforeach

    <div class="text-right mt-3">
        @foreach ($front->getTableButtons() as $name => $title)
            <button type="submit" class="btn btn-primary" @if (strlen($name) > 0) name="submitName" value="{{ $name }}" @endif>{!! $title !!}</button>
        @endforeach
    </div>

    {{ html()->form()->close() }}
@endsection
