<?php

namespace WeblaborMx\Front\Inputs;

class Money extends Input
{
    public $decimals = 2;

    public function form()
    {
        $this->attributes['step'] = $this->getStep();
        $input = html()
            ->number($this->getColumn(), $this->getDefaultValue())
            ->attributes($this->attributes);

        return InputGroup::make('$', $input)->form();
    }

    protected function getStep()
    {
        return 1 / (10 ** $this->decimals);
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        if (!is_numeric($value)) {
            return $value;
        }
        return '$' . number_format($value, $this->decimals);
    }

    public function decimals($decimals)
    {
        $this->decimals = $decimals;
        return $this;
    }
}
