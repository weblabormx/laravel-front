<?php

namespace WeblaborMx\Front\Inputs;

use WeblaborMx\Front\Texts\Heading;

class Checkboxes extends Input
{
    public $options = [];

    public function getValue($object)
    {
        $return = parent::getValue($object);
        $return = collect(json_decode($return));
        $return = $return->map(function ($item) {
            return "<li>".$item."</li>";
        });
        return '<ul>'.$return->implode('').'</ul>';
    }

    public function form()
    {
        return collect($this->options)->map(function ($value, $key) {
            $field = Check::make($value, $this->column.'['.$key.']');
            return $field->formHtml();
        })->implode('');
    }

    public function formHtml()
    {
        $title = Heading::make($this->title);
        return $title->form().$this->form();
    }

    public function options($array)
    {
        if (!$this->showOnHere()) {
            return $this;
        }
        if (is_callable($array)) {
            $array = $array();
        }
        $this->options = $array;
        return $this;
    }
}
