<?php

namespace WeblaborMx\Front\Inputs;

class Hidden extends Input
{
    public $show_on_index = false;
    public $show_on_show = false;
    public $needs_to_be_on_panel = false;
    public $show_on_filter = false;

    public function form()
    {
        return html()
            ->hidden($this->getColumn(), $this->getDefaultValue())
            ->attributes($this->attributes);
    }

    public function formHtml()
    {
        return $this->form();
    }
}
