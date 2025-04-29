<?php

namespace WeblaborMx\Front\Texts;

class Alert extends Text
{
    public $type = 'info';

    public function form()
    {
        $alert = $this;
        return view('front::texts.alert', compact('alert'))->render();
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
