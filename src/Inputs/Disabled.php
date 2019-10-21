<?php

namespace WeblaborMx\Front\Inputs;

class Disabled extends Input
{
	public function form()
	{
		$attributes = $this->attributes;
		$attributes['disabled'] = 'disabled';
		return \Form::text($this->column, $this->default_value, $attributes).\Form::hidden($this->column, $this->default_value, $this->attributes);
	}
}
