<?php

namespace WeblaborMx\Front\Inputs;

class Time extends Input
{
    public $pattern = '([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]';
    public $input_type = 'text';

    public function form()
    {
        $this->attributes['pattern'] = $this->pattern;
        $type = $this->input_type;

        if ($type === 'text') {
            $form = html()->text($this->column, $this->default_value);
        } elseif ($type === 'time') {
            $form = html()->time($this->column, $this->default_value);
        }

        return $form->attributes($this->attributes);
    }

    public function ignoreSeconds()
    {
        $this->pattern = '([01]?[0-9]|2[0-3]):[0-5][0-9]';
        $this->input_type = 'time';
        return $this;
    }
}
