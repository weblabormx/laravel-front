<?php

namespace WeblaborMx\Front\Texts;

class Heading extends Text
{
    public function form()
    {
        $heading = $this;
        return view('front::texts.heading', compact('heading'))->render();
    }
}
