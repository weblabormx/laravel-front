<?php

namespace WeblaborMx\Front\Inputs;

class MorphTo extends Input
{
	public $types;
	public $hide = false;

	public function types($types)
	{
		$this->types = collect($types)->map(function($item) {
			return new $item($this->source);
		})->mapWithKeys(function($item) {
			return [$item->label => $item->getModel()];
		});
		return $this;
	}

	public function getValue($object)
	{
		$relation = $this->column;
		if(!is_object($object->$relation)) {
			return '--';
		}

		$value = $object->$relation;
		if(!isset($value)) {
			return '--';
		}
		$class = get_class($value);
		$result = $this->types->search($class);
		if(!isset($result)) {
			return '--';
		}
		return $result.' #'.$value->getKey();
	}

	public function form()
	{
		if( $this->hide && ((request()->filled($this->column.'_type') && request()->filled($this->column.'_id')) || $this->source=='edit') ) {
			return collect([
				Hidden::make($this->title, $this->column.'_type'),
				Hidden::make($this->title.' Id', $this->column.'_id')
			])->map(function($item) {
				return (string) $item->formHtml();
			})->implode('');
		}
		return collect([
			Select::make($this->title, $this->column.'_type')->options($this->types->flip()),
			Text::make($this->title.' Id', $this->column.'_id')
		])->map(function($item) {
			return $item->formHtml();
		})->implode('');
	}

	public function formHtml()
	{
		return $this->form();
	}

	public function hideWhenValuesSet()
	{
		$this->hide = true;
		return $this;
	}
}
