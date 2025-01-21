<?php

namespace WeblaborMx\Front\Components;

class ShowCards extends Component
{
    public $cards;

    public function __construct($cards, $column = null, $extra = null, $source = null)
    {
        $this->cards = $cards;
    }

    public function form()
    {
        $cards = $this->cards;
        return view('front::components.cards', compact('cards'))->render();
    }
}
