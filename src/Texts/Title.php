<?php

namespace WeblaborMx\Front\Texts;

class Title extends Text
{
    public $size = 2;

    public function form()
    {
        $component = $this;
        return view('front::texts.title', compact('component'))->render();
    }

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }
}
