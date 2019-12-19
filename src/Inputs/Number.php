<?php

namespace WeblaborMx\Front\Inputs;

class Number extends Input
{
	public function form()
	{
		$this->attributes['step'] = 'any';
		return \Form::number($this->column, $this->default_value, $this->attributes);
	}
}
