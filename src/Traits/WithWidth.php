<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Str;

trait WithWidth
{
	public $width = 'full';

	public function bootstrap_width()
    {
        if(Str::contains($this->width, '/')) {
            $width = explode('/', $this->width);
            return round((12/$width[1])*$width[0]);
        }
        return 12;
    }

    public function style_width()
    {
        if($this->width=='full') {
            return;
        }
        return "width: calc({$this->width_porcentage()}% - 25px); display: inline-block; vertical-align:top; margin: 20px 10px;";
    }

    public function width_porcentage()
    {
        if($this->width=='1/2') {
            return 50;
        } else if($this->width=='1/3') {
            return 33;
        } else if($this->width=='1/4') {
            return 25;
        } else if($this->width=='3/4') {
            return 75;
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
