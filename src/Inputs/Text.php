<?php

namespace WeblaborMx\Front\Inputs;

class Text extends Input
{
    public function form()
    {
        return html()
            ->text($this->getColumn(), $this->getDefaultValue())
            ->attributes($this->attributes);
    }
}
