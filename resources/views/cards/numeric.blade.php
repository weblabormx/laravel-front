<div class="d-flex col-sm-{{$card->bootstrap_width()}} align-items-center">
    <div class="card-body media align-items-center text-body">
        @if(!is_null($card->getIcon()))
            <i class="{{$card->getIcon()}} display-4 d-block text-primary font-weight-bold"></i>
        @endif
        <span class="media-body d-block ml-3">
            <small class="text-muted text-center d-block mb-2">{!! $card->getText() !!}</small>
            <small class="float-right" style="padding-top: 5px;">
                @if($card->getPorcentage() > 0)
                    <i class='fa fa-arrow-alt-circle-up text-success'></i> {{$card->getPorcentage()}}% Increase
                @elseif($card->getPorcentage() < 0)
                    <i class='fa fa-arrow-alt-circle-down text-danger'></i> {{($card->getPorcentage())*-1}}% Decrease
                @endif
            </small>
            <span class="text-big">
            	<span class="font-weight-bolder">{!! $card->showNumber($card->getNumber()) !!}</span>
            	{!! $card->getSubtitle() !!}
            </span><br>
        </span>
    </div>
</div>
