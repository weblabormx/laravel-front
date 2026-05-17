<?php

namespace WeblaborMx\Front\Inputs;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Percentage extends Input
{
    public $decimals = null;

    public function form()
    {
        $this->attributes['step'] = '.01';

        $input = html()
            ->number($this->getColumn(), $this->getDefaultValue())
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
            return $value.'%';
        }

        return round($value, $this->decimals).'%';
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

        return ((float) $value) / 100;
    }

    public function excelFormat(): ?string
    {
        if (!is_null($this->excel_type)) {
            return $this->excel_type;
        }
        if (is_numeric($this->decimals) && (int) $this->decimals > 0) {
            return '0.'.str_repeat('0', (int) $this->decimals).'%';
        }

        return NumberFormat::FORMAT_PERCENTAGE;
    }
}
