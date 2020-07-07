@extends('front::layout')

@section('content')
    
    @include('front::elements.breadcrumbs')

    <h4 class="d-flex justify-content-between align-items-center mb-4">
        <div>{!! $front->getTitle($object) !!}</div>
        <div>
            @foreach($front->getLinks($object) as $button)
                {!! $button->form() !!}
            @endforeach
        </div>
    </h4>

    @foreach($front->showPanels() as $panel)
        {!! $panel->showHtml($object) !!}
    @endforeach

    @php $porcentage = 0; @endphp
    @foreach($front->showRelations() as $key => $relation)
        @php $porcentage += $relation->width_porcentage(); @endphp
        <div class="relation" style="{{$relation->style_width()}}">
            <div class="pb-4">
                <h4 class="d-flex justify-content-between align-items-center">
                    <div>{{$relation->title}}</div>
                    <div>
                        @foreach($relation->getLinks($object, $key, $relation->front) as $button)
                            {!! $button->form() !!}
                        @endforeach
                    </div>
                </h4>
                {!! $relation->getValue($object) !!}
            </div>
        </div>
        @if($porcentage>=100)
            @php $porcentage = 0; @endphp
            <div style="clear:both;"></div>
        @endif
    @endforeach
   
@endsection


