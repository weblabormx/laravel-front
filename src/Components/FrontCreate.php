<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Facades\Front;

class FrontCreate extends Component
{
    public $front_class;
    public $query;
    public $lense;
    public $base_url;

    public function __construct($front_class, $column = null, $extra = null, $source = null)
    {
        $this->source = $source;
        $this->front_class = Front::makeResource($front_class, $this->source);
        $this->show_before = $this->front_class->canCreate();
    }

    public function form()
    {
        $front = $this->front_class;
        if (isset($this->lense)) {
            $front = $front->getLense($this->lense);
        }
        if (isset($this->base_url)) {
            $front = $front->setBaseUrl($this->base_url);
        }
        return view('front::crud.partial-create', compact('front'))->render();
    }

    public function setLense($lense)
    {
        $this->lense = $lense;
        return $this;
    }

    public function formUrl($base_url)
    {
        $this->base_url = $base_url;
        return $this;
    }
}
