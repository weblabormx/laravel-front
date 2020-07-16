<?php

namespace WeblaborMx\Front\Inputs;

class ImageCropper extends Input
{
	public $image;
	public $ratio = null;
	public $width = 500;

	public function form()
	{
		$id = 'image-to-crop-'.rand(1, 10);
		$this->attributes['data-type'] = 'cropper';
		$this->attributes['data-image'] = $id;
		$this->attributes['data-width'] = $this->width;
		$this->attributes['data-ratio'] = $this->ratio;
		return '<img src="'.$this->image.'" id="'.$id.'" style="max-width: none !important;" />'.\Form::hidden($this->column, $this->default_value, $this->attributes);
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
}
