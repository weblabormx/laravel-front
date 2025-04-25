<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;

class Textarea extends Input
{
	public $limit_on_index = false;

	public function form()
	{
		return \WeblaborMx\Front\Facades\Form::textarea($this->column, $this->default_value, $this->attributes);
	}

	public function getValue($object)
	{
		$value = parent::getValue($object);
		if ($this->limit_on_index != false && $this->source == 'index') {
			return Str::limit($value, $this->limit_on_index);
		}
		return nl2br($value);
	}

	public function limitOnIndex($limit = 80)
	{
		$this->limit_on_index = $limit;
		return $this;
	}
}
