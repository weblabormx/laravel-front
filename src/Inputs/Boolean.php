<?php

namespace WeblaborMx\Front\Inputs;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Boolean extends Input
{
    public $true_value = 1;

    public $false_value = 0;

    public function form()
    {
        $value = $this->getDefaultValue();

        return html()
            ->checkbox(
                $this->getColumn(),
                ! is_null($value) ? $value == $this->true_value : null,
                $this->true_value
            );
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        if ($value === '--') {
            return $value;
        }
        if ($this->source == 'index') {
            if ($value) {
                return '<span style="color: #2cbb7d;">✔</span>';
            }

            return '<span style="color: #e74344;">✘</span>';
        }
        if ($value == $this->true_value) {
            return '<span style="color: #2cbb7d; padding-right: 7px;">✔</span> '.__('Yes');
        }

        return '<span style="color: #e74344; padding-right: 10px;">✘</span> '.__('No');
    }

    public function getExcelValue($object)
    {
        $value = $this->getRawValue($object);
        if (is_null($value) || $value === '') {
            return null;
        }

        return $value == $this->true_value ? 1 : 0;
    }

    public function parseExcelValue($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        return (int) $value === 1 ? $this->true_value : $this->false_value;
    }

    public function excelFormat(): ?string
    {
        return $this->excel_type ?? NumberFormat::FORMAT_NUMBER;
    }

    public function setTrueValue($value)
    {
        $this->true_value = $value;

        return $this;
    }

    public function setFalseValue($value)
    {
        $this->false_value = $value;

        return $this;
    }

    public function processData($data)
    {
        $data = collect($data)->dot();
        if (! isset($data[$this->column])) {
            $data[$this->column] = $this->false_value;
        }
        $data = $data->undot()->all();

        return $data;
    }
}
