<?php

namespace WeblaborMx\Front\Inputs;

class Autocomplete extends Input
{
	public $url;
	public $text;

	public function form()
	{
		$this->attributes['data-type'] = 'autocomplete';
		$this->attributes['src'] = $this->url;
		$value = isset($this->default_value) ? $this->default_value : \Form::getValueAttribute($this->column);
		if (!is_null($value)) {
			$this->attributes['data-selected-value'] = $value;
			// Fill text
			if (isset($this->text)) {
				$this->attributes['data-selected-text'] = $this->text;
			} else {
				$value = \Form::getValueAttribute($this->column . '_text');
				$this->attributes['data-selected-text'] = $value;
			}
		}
		if ($this->source == 'create' || $this->source == 'edit') {
			$this->attributes['data-text-input'] = 'false';
		}

		if (isset($this->attributes['disabled'])) {
			return \Form::text($this->column . '_hidden', $this->attributes['data-selected-text'] ?? false, ['disabled' => 'disabled']);
		}

		return \Form::text($this->column, $this->default_value, $this->attributes);
	}

	public function setUrl($url)
	{
		$this->url = url($url);
		return $this;
	}

	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}
}
