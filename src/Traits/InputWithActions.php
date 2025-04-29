<?php

namespace WeblaborMx\Front\Traits;

trait InputWithActions
{
    public $actions = [];

    public function addActions($actions)
    {
        $this->actions = $actions;
        return $this;
    }
}
