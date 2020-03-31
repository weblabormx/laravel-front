@extends('front::layout')

@section('sidebar')

    @if(count($front->filters())>0)
        <div class="sidenav-header small font-weight-semibold mb-2 text-uppercase">{{ __('Options') }}</div>
        {!! Form::open(['url' => request()->url(), 'method' => 'get']) !!} 
            <div class="card pt-3 sidenav-forms">
                @foreach($input->getMassiveForms() as $form)
                    {!! $form->formHtml() !!}
                @endforeach
            </div>
            {!! Form::submit(__('Search'), ['class' => 'btn btn-secondary btn-sm btn-block']) !!}
        {!! Form::close() !!}
    @endif
    
@endsection
    
@section('content')
    
    @include('front::elements.breadcrumbs', ['data' => ['massive' => $input]])
    @include ('front::elements.errors')


    <h4 class="font-weight-bold py-3">{{__('Edit')}} {{$input->title}}</h4>

    {!! Form::open(array('url' => request()->url(), 'files' => true)) !!}

        <div class="table-responsive">
            <table class="table table-striped bg-white">
                <thead class="thead-dark">
                    <tr>
                        @foreach($input->getTableHeadings($object) as $title)
                            <th>{{$title}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($result as $object)
                        <tr>
                            @foreach($input->getTableValues($object) as $value)
                                <td>{!! $value !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    @foreach($input->getExtraTableValues($object) as $row)
                        <tr>
                            @foreach($row as $value)
                                <td>{{$value}}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @foreach(request()->except('rows') as $key => $value)
            {!! Form::hidden($key) !!}
        @endforeach

        <div class="text-right mt-3">
            @foreach($input->getTableButtons() as $name => $title)
                <button type="submit" class="btn btn-primary" @if(strlen($name)>0) name="submitName" value="{{$name}}" @endif>{!! $title !!}</button>
            @endforeach
        </div>

    {!! Form::close() !!}

@endsection
