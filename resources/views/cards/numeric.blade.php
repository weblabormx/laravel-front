<div class="d-flex col-sm-{{$card->bootstrap_width()}} align-items-center">
    <div class="card-body media align-items-center text-body">
        @if(!is_null($card->getIcon()))
            @if(!is_null($card->link()))
                <a href="{{$card->link()}}">
            @endif
            <i class="{{$card->getIcon()}} display-4 d-block text-primary font-weight-bold"></i>
            @if(!is_null($card->link()))
                </a>
            @endif
        @endif
        <span class="media-body d-block ml-3">
            <span class="text-big">
            	<span class="font-weight-bolder">{!! $card->showNumber($card->getNumber()) !!}</span>
            	{!! $card->getSubtitle() !!}
            </span><br>
            <small class="float-right">
                @if($card->getPorcentage() > 0)
                    <i class='fa fa-arrow-alt-circle-up text-success'></i> {{$card->getPorcentage()}}% Increase
                @elseif($card->getPorcentage() < 0)
                    <i class='fa fa-arrow-alt-circle-down text-danger'></i> {{($card->getPorcentage())*-1}}% Decrease
                @endif
            </small>
            <small class="text-muted">{!! $card->getText() !!}</small>
        </span>
    </div>
</div>
