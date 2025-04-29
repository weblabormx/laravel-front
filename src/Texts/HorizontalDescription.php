<?php

namespace WeblaborMx\Front\Texts;

class HorizontalDescription extends Text
{
    public function load()
    {
        $this->data = $this->title;
    }

    public function form()
    {
        $horizontal_description = $this;
        return view('front::texts.horizontal-description', compact('horizontal_description'))->render();
    }
}
