<?php

namespace WeblaborMx\Front\Components;

class View extends Component
{
    private $with = [];

    public function form()
    {
        return view($this->column, $this->with)->render();
    }

    public function with($array)
    {
        $this->with = $array;
        return $this;
    }
}
