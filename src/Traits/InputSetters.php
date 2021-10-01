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
	public $default_value_force = false;
	public $hide = false;
	
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

	public function conditionalOld($column, $value)
	{
		// This work on form
		$this->form_before = '<div data-type="conditional" data-cond-option="'.$column.'" data-cond-value="'.$value.'" style="'.$this->style_width().'">';
		$this->form_after = '</div>';
		$this->conditional = $column.'='.$value;
		return $this;
	}

	public function conditional($conditional)
	{
		// This work on form
		$this->form_before = '<div data-type="conditional2" data-condition="'.$conditional.'" style="'.$this->style_width().'">';
		$this->form_after = '</div>';
		$this->conditional = $conditional;
		return $this;
	}

	private function validateConditional($object)
	{
		if(isset($this->conditional)) {
			$conditional = '$object->'.$this->conditional;
			try {
				$conditional = eval("return $conditional;");
				return $conditional;
			} catch (\Exception $e) {
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

	public function withId($id)
	{
		$this->attributes['id'] = $id;
		return $this;
	}

	public function default($value, $force = false)
	{
		$this->default_value_force = $force;
		if($this->source!='create' && !$force) {
			return $this;
		}
		if(!is_string($value) && is_callable($value)) {
			$value = $value();
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

	public function hideWhenValuesSet()
	{
		$this->hide = true;
		return $this;
	}
}
