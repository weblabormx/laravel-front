<?php

namespace WeblaborMx\Front\Inputs;

use DateTime as PhpDateTime;
use Carbon\Carbon;

class DateTime extends Input
{
	public $pattern = null;
	public $input_type = 'datetime-local';
	public $is_time = false;

	public function form()
	{
		$this->attributes['step'] = $this->attributes['step'] ?? 'any';
		$this->attributes['pattern'] = $this->pattern;
		$value = $this->getValue($this->resource->object);

		// Parse value if it's a string and not already a DateTime object
        if (!is_null($value) && !$value instanceof PhpDateTime && !$value instanceof Carbon && is_string($value)) {
            try {
                $value = Carbon::parse($value);
            } catch (\Exception $e) {
                // Keep original string if parsing fails, Form helper might handle it
            }
        }

		// Format for input if it's a DateTime object
        if ($value instanceof Carbon || $value instanceof PhpDateTime) {
            $value = $value->format('Y-m-d\TH:i:s');
        }

		return \WeblaborMx\Front\Facades\Form::datetimeLocal($this->getColumn(), $value, $this->attributes);
	}

	public function processData($data)
	{
		if (!isset($data[$this->column])) {
			return $data; // Return original data if column not set
		}

		// Try to parse the datetime string
        try {
            $parsedDate = Carbon::parse($data[$this->column]);
            // Format to standard Y-m-d H:i:s for database storage
            $data[$this->column] = $parsedDate->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // If parsing fails, set to null or keep original? Let's set null.
            // Or maybe keep the original string if it couldn't be parsed?
            // For now, let's keep the original string to avoid data loss.
            // $data[$this->column] = null;
        }
        
        return $data;
	}

	public function getValue($object = null)
	{
		$value = parent::getValue($object);
        // Return formatted date for display or input value
        if ($value instanceof Carbon || $value instanceof PhpDateTime) {
            return $value->format('Y-m-d\TH:i:s');
        }
        // Attempt to parse if it's a string
        if (is_string($value)) {
            try {
                return Carbon::parse($value)->format('Y-m-d\TH:i:s');
            } catch (\Exception $e) {
                // Return original string if parsing fails
            }
        }
		return $value;
	}

	public function useSeconds()
	{
		$this->pattern = '^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$';
		// $this->input_type = 'datetime'; // This line is no longer needed
		return $this;
	}
}
