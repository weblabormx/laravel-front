<?php

namespace WeblaborMx\Front\Inputs;

class Text extends Input
{
	public function form()
	{
		return \WeblaborMx\Front\Facades\Form::text($this->getColumn(), $this->default_value, $this->attributes);
	}
}
