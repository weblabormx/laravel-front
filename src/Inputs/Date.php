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
        return html()
            ->date($this->column, $this->default_value)
            ->attributes($this->attributes);
    }

    public function getValue($object)
    {
		$column = $this->column;
		$value = $object->$column;
		if ($value instanceof Carbon) {
			return $value->format(config('front.date_format'));
		}

        $value = parent::getValue($object);
        if (is_object($value)) {
            return $value->format('Y-m-d');
        }
        return $value;
    }
}
