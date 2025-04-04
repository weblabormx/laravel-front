<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Carbon;

class Date extends Input
{
	public function form()
	{
		if($this?->resource?->object ?? false) {
			$this->default_value = $this->getValue($this->resource->object);
		}
		return \Form::date($this->column, $this->default_value, $this->attributes);
	}

	public function getValue($object)
	{
		$column = $this->column;
		$value = $object->$column;
		if ($value instanceof Carbon) {
			return $value->format(config('front.date_format'));
		}

		$value = parent::getValue($object);
		return $value;
	}
}
