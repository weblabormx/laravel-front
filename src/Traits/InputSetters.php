<?php

namespace WeblaborMx\Front\Traits;

trait InputSetters
{
	public $default_value = null;
	public $attributes = ['class' => 'form-control'];
	public $conditional;
	public $resource;
	public $display_using;
	public $link;
	public $class = '';

	public function setColumn($value)
	{
		$this->column = $value;
		return $this;
	}

	public function setBefore($value)
	{
		$this->form_before = $value;
		return $this;
	}

	public function setAfter($value)
	{
		$this->form_after = $value;
		return $this;
	}

	public function setSource($value)
	{
		$this->source = $value;
		return $this;
	}

	public function style($css) 
	{
		$this->attributes['style'] = $css;
		return $this;
	}

	public function withLink($link)
	{
		if(is_callable($link)) {
			try {
				$link = $link();
			} catch (\Exception $e) {
				$link = null;
			}
		}
		if(!is_null($link) && strlen($link) > 0) {
			$this->link = $link;	
		}
		return $this;
	}

	public function disabled()
	{
		$this->attributes['disabled'] = 'disabled';
		return $this;
	}

	public function sortable()
	{
		// Do nothing
		return $this;
	}

	public function conditional($key, $value)
	{
		// This work on form
		$this->form_before = '<div data-type="conditional" data-cond-option="'.$key.'" data-cond-value="'.$value.'" style="'.$this->style_width().'">';
		$this->form_after = '</div>';
		$this->conditional = ['key' => $key,  'value' => $value];
		return $this;
	}

	private function validateConditional($object)
	{
		if(isset($this->conditional)) {
			$conditional = $this->conditional;
			$key = $conditional['key'];
			if( !isset($object->$key) || ( isset($object->$key) && $object->$key != $conditional['value'] ) ) {
				return false;
			}
		}
		return true;
	}

	public function center()
	{
		$this->data_classes = 'center';
		return $this;
	}

	public function help($help)
	{
		$this->help = $help;
		return $this;
	}

	public function displayUsing($display_using)
	{
		$this->display_using = $display_using;
		return $this;
	}

	public function setResource($resource)
	{
		$this->resource = $resource;
		return $this;
	}

	public function withMeta($attributes)
	{
		$attributes = collect($this->attributes)->merge($attributes)->toArray();
		$this->attributes = $attributes;
		return $this;
	}

	public function placeholder($placeholder)
	{
		$this->attributes['placeholder'] = $placeholder;
		return $this;
	}

	public function addAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
		return $this;
	}

	public function default($value, $force = false)
	{
		if(!is_string($value) && is_callable($value)) {
			$value = $value();
		}
		if($this->source!='create' && !$force) {
			return $this;
		}
		$this->default_value = $value;
		return $this;
	}

	public function class($class)
	{
		$this->class = $class;
		return $this;
	}

	public function editRules($rules)
	{
		return $rules;
	}
}
