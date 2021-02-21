<?php

namespace WeblaborMx\Front\Inputs;

class Percentage extends Input
{
	public function form()
	{
		$this->attributes['step'] = '.01';
		$input = \Form::number($this->column, $this->default_value, $this->attributes);
		return InputGroup::make(null, $input, '%')->form();
	}

	public function getValue($object)
	{
		$value = parent::getValue($object);
		if(!is_numeric($value)) {
			return $value;
		}
		return $value.'%';
	}
}
