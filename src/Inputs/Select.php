<?php

namespace WeblaborMx\Front\Inputs;

class Select extends Input
{
	public $options = [];
	public $empty_title = 'Pick one..';

	public function getValue($object)
	{
		$value = parent::getValue($object);
		$options = $this->options;
		return $options[$value] ?? $value;
	}


	public function form()
	{
		$this->attributes['placeholder'] = __($this->empty_title);
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

	public function setEmptyTitle($value = null)
	{
		if(is_null($value)) 
		{
			return $this;
		}
		
		$this->empty_title = $value;
		return $this;
	}
}
