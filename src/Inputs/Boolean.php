<?php

namespace WeblaborMx\Front\Inputs;

class Boolean extends Input
{
    public $true_value = 1;
    public $false_value = 0;

    public function form()
    {
        return html()
            ->checkbox(
                $this->column,
                !is_null($this->default_value) ? $this->default_value == $this->true_value : null,
                $this->true_value
            );
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        $value = $value === '--' ? false : $value;
        if ($this->source == 'index') {
            if ($value) {
                return '<span style="color: #2cbb7d;">✔</span>';
            }
            return '<span style="color: #e74344;">✘</span>';
        }
        if ($value == $this->true_value) {
            return '<span style="color: #2cbb7d; padding-right: 7px;">✔</span> ' . __('Yes');
        }
        return '<span style="color: #e74344; padding-right: 10px;">✘</span> ' . __('No');
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
        if (!isset($data[$this->column])) {
            $data[$this->column] = $this->false_value;
        }
        return $data;
    }
}
