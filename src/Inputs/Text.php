<?php

namespace WeblaborMx\Front\Inputs;

class Text extends Input
{
	public function form()
	{
		return \Form::text($this->column, $this->default_value, $this->attributes);
	}
}
