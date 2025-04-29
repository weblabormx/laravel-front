<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Facades\Front;

class FrontIndex extends Component
{
    public $front_class;
    public $query;
    public $lense;

    public function __construct($front_class, $column = null, $extra = null, $source = null)
    {
        $this->source = $source;
        $this->front_class = Front::makeResource($front_class, $this->source);
        $this->show_before = $this->front_class->canIndex();
    }

    public function form()
    {
        $front = $this->front_class;
        if (isset($this->lense)) {
            $front = $front->getLense($this->lense);
        }
        $query = $front->globalIndexQuery();
        if (isset($this->query)) {
            $function = $this->query;
            $query = $function($query);
        }
        $result = $query->get();
        $style = 'margin-bottom: 30px;';
        return view('front::crud.partial-index', compact('result', 'front', 'style'))->render();
    }

    public function setRequest($request)
    {
        request()->request->add($request);
        return $this;
    }

    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

    public function setLense($lense)
    {
        $this->lense = $lense;
        return $this;
    }
}
