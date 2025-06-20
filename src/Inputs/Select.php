<?php

namespace WeblaborMx\Front\Inputs;

class Select extends Input
{
    public $options = [];
    public $empty_title = 'Pick one..';
    public $show_placeholder = true;

    public function getValue($object)
    {
        $value = parent::getValue($object);
        $options = $this->options;
        return $options[$value] ?? $value;
    }

    public function form()
    {
        $select = html()
            ->select($this->column, $this->options)
            ->attributes($this->attributes);

        if($this->default_value) {
            $select = $select->value($this->default_value);
        }
        if ($this->show_placeholder) {
            $select = $select->placeholder(__($this->empty_title));
        }

        return $select;
    }

    public function options($array)
    {
        if (!$this->showOnHere()) {
            return $this;
        }
        if (is_callable($array)) {
            $array = $array();
        }
        $this->options = collect($array)->map(function ($item) {
            if (!is_string($item)) {
                return $item;
            }
            return __($item);
        });
        return $this;
    }

    public function setEmptyTitle($value = null)
    {
        if (is_null($value)) {
            return $this;
        }

        $this->empty_title = $value;
        return $this;
    }

    public function multiple()
    {
        $this->attributes['multiple'] = 'multiple';
        $this->column = $this->column . '[]';
        $this->hidePlaceholder();
        return $this;
    }

    public function hidePlaceholder()
    {
        $this->show_placeholder = false;
        return $this;
    }

    public function setPlaceholder($value)
    {
        $this->show_placeholder = $value;
        return $this;
    }
}
