<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Inputs\Input;
use WeblaborMx\Front\Traits\WithWidth;

class Component extends Input
{
    use WithWidth;

    public $is_input = false;

    public function formHtml()
    {
        return $this->form();
    }

    public function showHtml($object)
    {
        return $this->form();
    }
}
