<?php

namespace WeblaborMx\Front\Inputs;

class Disabled extends Input
{
    public $process_input = true;

    public function form()
    {
        $attributes = $this->attributes;
        $attributes['disabled'] = 'disabled';

        return html()
            ->text($this->column, $this->default_value)
            ->attributes($attributes)
            .
            html()
            ->hidden($this->column, $this->default_value)
            ->attributes($this->attributes);
    }

    public function processData($data)
    {
        if (!$this->process_input) {
            unset($data[$this->column]);
        }
        return $data;
    }

    // To avoid saving the input data
    public function processInput($value)
    {
        $this->process_input = $value;
        return $this;
    }
}
