<?php

namespace WeblaborMx\Front\Inputs;

class Number extends Input
{
	public function form()
	{
		return \Form::number($this->column, $this->default_value, $this->attributes);
	}
}
