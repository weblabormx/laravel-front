<?php

namespace WeblaborMx\Front\Components;

class Welcome extends Component
{
    public $date_format = 'l, F j, Y';

    public function form()
    {
        $component = $this;
        return view('front::components.welcome', compact('component'))->render();
    }

    public function setDateFormat($format)
    {
        $this->date_format = $format;
        return $this;
    }
}
