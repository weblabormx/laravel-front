<?php

namespace WeblaborMx\Front\Inputs;

class DateTime extends Input
{
	public function form()
	{
		$this->attributes['pattern'] = '^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$';
		return \Form::datetime($this->column, $this->default_value, $this->attributes);
	}
}
