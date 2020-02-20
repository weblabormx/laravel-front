<?php

namespace WeblaborMx\Front\Inputs;

class Time extends Input
{
	public function form()
	{
		$this->attributes['pattern'] = '([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]';
		return \Form::text($this->column, $this->default_value, $this->attributes);
	}
}
