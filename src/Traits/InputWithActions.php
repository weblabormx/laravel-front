<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Front;

trait InputWithActions
{
    public $actions = [];

    public function addActions($actions)
    {
        $this->actions = $actions;
        return $this;
    }
}
