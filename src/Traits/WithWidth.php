<?php

namespace WeblaborMx\Front\Traits;

trait WithWidth
{
	public $width = 'full';

	public function bootstrap_width()
    {
        if($this->width=='1/2') {
            return 6;
        } else if($this->width=='1/3') {
            return 4;
        } else if($this->width=='1/4') {
            return 3;
        } else if($this->width=='3/4') {
            return 9;
        }
        return 12;
    }

    public function style_width()
    {
        if($this->width=='full') {
            return;
        }
        return "width: calc({$this->porcentage()}% - 25px); display: inline-block; vertical-align:top; margin: 20px 10px;";
    }

    public function porcentage()
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
