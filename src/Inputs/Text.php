<?php

namespace WeblaborMx\Front\Inputs;

class Text extends Input
{
	public function form()
	{
		if(isset($this->size)) {
			$this->attributes['style'] = 'width: '.$this->size.'px';
		}
		return \Form::text($this->column, $this->default_value, $this->attributes);
	}

	public function size($size)
	{
		$this->size = $size;
		return $this;
	}
}
