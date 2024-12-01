<?php

namespace WeblaborMx\Front\Inputs;

class Password extends Input
{
    public $show_on_index = false;
    public $show_on_show = false;

    public function form()
    {
        return html()
            ->password($this->column)
            ->attributes($this->attributes);
    }
}
