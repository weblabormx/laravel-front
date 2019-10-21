<dl class="row">
    @foreach($horizontal_description->data as $title => $value)
        <dt class="col-sm-2">{!! $title !!}</dt>
        <dd class="col-sm-10">{!! $value !!} </dd>
    @endforeach
</dl>