<?php

namespace WeblaborMx\Front\Texts;

use WeblaborMx\Front\Front;

class Paragraph extends Text
{
    public function form()
    {
        $paragraph = $this;
        return view('front::texts.paragraph', compact('paragraph'))->render();
    }
}
