<?php

namespace WeblaborMx\Front\Inputs;

class ImageCropper extends Input
{
	public $image;
	public $handler;
	public $ratio = null;
	public $width = 500;

	public function form()
	{
		$rand = rand(1, 100);
		$id = 'image-to-crop-'.$rand;
		$function = "handler{$rand}";
		$this->attributes['data-type'] = 'cropper';
		$this->attributes['data-image'] = $id;
		$this->attributes['data-width'] = $this->width;
		$this->attributes['data-ratio'] = $this->ratio;
		if(isset($this->handler)) {
			$this->attributes['data-handler'] = $function;
		}
		$html = '<img src="'.$this->image.'" id="'.$id.'" style="max-width: none !important;" />';
		$html .= \Form::hidden($this->column, $this->default_value, $this->attributes);
		if(isset($this->handler)) {
			$handler = $this->handler;
			$html .= '<script type="text/javascript">function '.$function.'(c) { '.$handler().' }</script>';
		}
		return $html;
	}

	public function setImage($image)
	{
		$this->image = $image;
		return $this;
	}

	public function setWidth($width)
	{
		$this->width = $width;
		return $this;
	}

	public function setRatio($ratio)
	{
		$this->ratio = $ratio;
		return $this;
	}

	public function setHandler($handler)
	{
		$this->handler = $handler;
		return $this;
	}
}
