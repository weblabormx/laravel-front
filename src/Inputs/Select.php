<?php

namespace WeblaborMx\Front\Inputs;

class Select extends Input
{
	public $options = [];

	public function getValue($object)
	{
		$value = parent::getValue($object);
		$options = $this->options;
		return $options[$value] ?? $value;
	}


	public function form()
	{
		$this->attributes['placeholder'] = 'Pick one..';
		return \Form::select($this->column, $this->options, $this->default_value, $this->attributes);
	}

	public function options($array)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		if(is_callable($array)) {
			$array = $array();
		}
		$this->options = $array;
		return $this;
	}
}
