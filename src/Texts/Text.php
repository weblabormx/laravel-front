<?php

namespace WeblaborMx\Front\Texts;

use WeblaborMx\Front\Inputs\Input;

class Text extends Input
{
    public $is_input = false;
    public $needs_to_be_on_panel = false;
    public $text;

    public function formHtml()
    {
        return $this->form();
    }

    public function showHtml($object)
    {
        return $this->formHtml();
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }
}
