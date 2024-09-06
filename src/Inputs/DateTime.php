<?php

namespace WeblaborMx\Front\Inputs;

use Carbon\Carbon;

class DateTime extends Input
{
	public $pattern = null;
	public $input_type = 'frontDatetime';

	public function form()
	{
		$this->attributes['pattern'] = $this->pattern;

		return html()
			->datetime($this->column, $this->default_value)
			->attributes($this->attributes);
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
