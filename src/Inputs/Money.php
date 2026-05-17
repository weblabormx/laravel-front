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

        return '$'.number_format($value, $this->decimals);
    }

    public function getExcelValue($object)
    {
        $value = $this->getRawValue($object);
        if (is_null($value) || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return parent::getExcelValue($object);
        }

        return round((float) $value, $this->decimals);
    }

    public function parseExcelValue($value)
    {
        if (is_string($value)) {
            $value = str_replace(['$', ',', ' '], '', $value);
        }

        return is_numeric($value) ? (float) $value : $value;
    }

    public function excelFormat(): ?string
    {
        if (!is_null($this->excel_type)) {
            return $this->excel_type;
        }

        $decimals = max(0, (int) $this->decimals);

        return '$#,##0'.($decimals > 0 ? '.'.str_repeat('0', $decimals) : '');
    }

    public function decimals($decimals)
    {
        $this->decimals = $decimals;

        return $this;
    }
}
