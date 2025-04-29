<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Str;

trait WithWidth
{
    public $width = 'full';

    public function bootstrap_width()
    {
        if (Str::contains($this->width, '/')) {
            $width = explode('/', $this->width);
            return round((12 / $width[1]) * $width[0]);
        }
        return 12;
    }

    public function style_width()
    {
        if ($this->width == 'full') {
            return "width: 100%";
        }
        return "width: calc({$this->width_porcentage()}% - 25px); display: inline-block; vertical-align:top; margin: 20px 10px;";
    }

    public function width_porcentage()
    {
        if (Str::contains($this->width, '/')) {
            $width = explode('/', $this->width);
            return round((100 / $width[1]) * $width[0]);
        }
        return 100;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function html()
    {
        $input = $this;
        $value = $this->form();
        return view('front::input-outer', compact('value', 'input'))->render();
    }
}
