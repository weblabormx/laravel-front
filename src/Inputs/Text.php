<?php

namespace WeblaborMx\Front\Inputs;

class Text extends Input
{
    public function form()
    {
        return html()
            ->text($this->getColumn(), $this->default_value)
            ->attributes($this->attributes);
    }
}
