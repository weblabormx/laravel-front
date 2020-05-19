@if($result->count() > 0)
    @php $helper = $front->getPartialIndexHelper($result, $pagination_name ?? null); @endphp
    <div class="card" @isset($style) style="{{$style}}" @endisset>
        {{ $helper->links() }}
        <div class="card-datatable table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        @foreach($helper->headers() as $field)
                            <th class="{{$field->class}}">{{$field->title}}</th>
                        @endforeach
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($helper->rows() as $row)
                        <tr>
                            @foreach($row->columns as $field)
                                <td class="{{$field->class}}">
                                    {!! $field->value !!}
                                </td>
                            @endforeach
                            @include('front::elements.object_actions', ['base_url' => $front->getBaseUrl(), 'object' => $row->object])
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $helper->links() }}
    </div>
@else
    <div class="card" @isset($style) style="{{$style}}" @endisset>
        <div class="card-body">
            {{ __('No data to show') }}
        </div>
    </div>
@endif
    
