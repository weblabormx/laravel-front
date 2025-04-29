<?php

namespace WeblaborMx\Front\Inputs;

class Code extends Input
{
    public $show_on_index = false;
    public $lang = 'html';

    public function form()
    {
        $this->attributes['data-type'] = 'codeeditor';
        $this->attributes['data-color'] = 'black';
        $this->attributes['data-lang'] = $this->lang;

        return html()
            ->textarea($this->column, $this->default_value)
            ->attributes($this->attributes);
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        if ($this->lang == 'json') {
            $value = json_decode($value);
            $value = json_encode($value, JSON_PRETTY_PRINT);
        }
        return '<code data-type="codeeditor" data-lang="' . $this->lang . '">' . $value . '</code>';
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }
}
