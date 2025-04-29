<?php

namespace WeblaborMx\Front\Inputs;

class Number extends Input
{
    public $decimals = null;

    public function form()
    {
        $this->attributes['step'] = $this->getStep();

        return html()
            ->number($this->column, $this->default_value)
            ->attributes($this->attributes);
    }

    public function decimals($decimals)
    {
        $this->decimals = $decimals;
        return $this;
    }

    private function getStep()
    {
        if (is_null($this->decimals)) {
            return 'any';
        }
        if ($this->decimals == 0) {
            return 1;
        }
        return 1 / pow(10, $this->decimals);
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        if (is_null($this->decimals) || !is_numeric($value) || !is_numeric($this->decimals)) {
            return $value;
        }
        return round($value, $this->decimals);
    }
}
