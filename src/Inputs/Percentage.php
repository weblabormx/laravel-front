<?php

namespace WeblaborMx\Front\Inputs;

class Percentage extends Input
{
    public $decimals = null;

    public function form()
    {
        $this->attributes['step'] = '.01';

        $input = html()
            ->number($this->column, $this->default_value)
            ->attributes($this->attributes);

        return InputGroup::make(null, $input, '%')->form();
    }

    public function decimals($decimals)
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        if (is_null($this->decimals) || !is_numeric($value) || !is_numeric($this->decimals)) {
            return $value . '%';
        }
        return round($value, $this->decimals) . '%';
    }
}
