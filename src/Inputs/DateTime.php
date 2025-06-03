<?php

namespace WeblaborMx\Front\Inputs;

use Carbon\Carbon;

class DateTime extends Input
{
    public $pattern = null;
    public $input_type = 'frontDatetime';

    public function form()
    {
		if($this?->resource?->object) {
			$this->default_value = $this->getValue($this->resource->object);
		}
        $this->attributes['pattern'] = $this->pattern;

        return html()
            ->datetime($this->column, $this->default_value)
            ->attributes($this->attributes);
    }

    public function getValue($object)
    {
		$column = $this->column;
		$value = $object->$column;
        if ($value instanceof Carbon && config('front.datetime_wrap')) {
            $value = $value->{config('front.datetime_wrap')}();
        }
		if ($value instanceof Carbon) {
			return $value->format(config('front.datetime_format'));
		}

        return parent::getValue($object);
    }

    public function useSeconds()
    {
        $this->pattern = '^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$';
        $this->input_type = 'datetime';
        return $this;
    }

    public function processData($data)
    {
        if (!isset($data[$this->column]) || $this->input_type != 'frontDatetime') {
            return $data;
        }

        $data[$this->column] = Carbon::parse($data[$this->column])->format('Y-m-d H:i:s');
        return $data;
    }
}
