<?php

namespace WeblaborMx\Front\Inputs;

class Date extends Input
{
	public function form()
	{
		return \Form::date($this->column, $this->default_value, $this->attributes);
	}

	public function getValue($object)
	{
		$value = parent::getValue($object);
		if(is_object($value)) {
			return $value->format('Y-m-d');
		}
		return $value;
	}
}
