<?php

namespace WeblaborMx\Front\Inputs;

class Text extends Input
{
    public function form()
    {
        $column = $this->getColumn();
        return html()
            ->text($column, request()->$column ?? $this->default_value)
            ->attributes($this->attributes);
    }
}
