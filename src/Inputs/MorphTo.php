<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Str;

class MorphTo extends Input
{
	public $types;
	public $types_models;
	public $hide = false;

	public function types($types)
	{
		// Create the types as front objects
		$this->types = collect($types)->map(function($item) {
			return new $item($this->source);
		});

		// Detect aliases
		$loader = AliasLoader::getInstance();
		$aliases = collect($loader->getAliases());

		// Make a new array with all models of the types
		$this->types_models = $this->types->mapWithKeys(function($item) use ($aliases) 
		{
			$alias = $item->getModel();

			// If model has alias use alias instead of model
			if($aliases->contains($item->getModel())) {
				$alias = $aliases->search($item->getModel());
			}

			return [$item->label => $alias];
		});

		// Return object
		return $this;
	}

	private function getFrontOnClass($class)
	{
		return $this->types->filter(function($item) use ($class) {
			return $item->getModel() == $class;
		})->first();
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
		if(!isset($this->types_models)) {
			abort(405, 'Please defines the possible types for the polimorfic values of '.$this->column.' with types() function');
		}
		$result = $this->types_models->search($class);
		if(!isset($result)) {
			return '--';
		}
		$front = $this->getFrontOnClass($class);
		if(is_null($front)) {
			abort(405, $class.' front is not defined on the types');
		}
		$this->link = $front->base_url.'/'.$value->getKey();
		$title_field = $front->title;
		return $value->$title_field;
	}

	public function form()
	{
		// if is hidden
		if( $this->hide && ((request()->filled($this->column.'_type') && request()->filled($this->column.'_id')) || $this->source=='edit') ) {
			return collect([
				Hidden::make($this->title, $this->column.'_type'),
				Hidden::make($this->title, $this->column.'_id')
			])->map(function($item) {
				return (string) $item->formHtml();
			})->implode('');
		}

		// Get options for the type select
		$options = $this->types_models->flip();

		// Add type select to fields
		$fields = collect([
			Select::make($this->title.' '.__('Type'), $this->column.'_type')->options($options),
		]);

		// Add every type field
		foreach ($this->types as $type) 
		{
			// Generate new field name
			$column = $this->column.'_id_'.strtolower($type->label); 

			// Get model
			$model = $options->search($type->label);
			$morph_field = $this->column;
			$type_field = $this->column.'_type';
			$id_field = $this->column.'_id';
			$title = $type->title;

			// Show autocomplete field
			$field = Autocomplete::make($type->label, $column)
				->setUrl($type->base_url.'/search')->conditional($type_field, $model);

			// if we have a value set it and its for this type
			if(isset($this->resource) && isset($this->resource->object) && $this->resource->object->$type_field == $model) {
				$field = $field->setText($this->resource->object->$morph_field->$title)->setValue($this->resource->object->$id_field);
			}

			// Add to fields array
			$fields[] = $field;
		}

		// Returns html
		return $fields->map(function($item) {
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

	public function editRules($rules)
	{
		// Dont do anything if there isnt any rule to this element
		if(!isset($rules[$this->column])) {
			return;
		}

		$rule = $rules[$this->column];
		unset($rules[$this->column]);
		$rules[$this->column.'_type'] = $rule;
		$rules[$this->column.'_id'] = $rule;
		return $rules;
	}

	public function processData($data)
	{
		$type_field = $this->column.'_type';
		$id_field = $this->column.'_id';
		if(!isset($data[$type_field])) {
			return $data;
		}

		$type = strtolower($data[$type_field]);
		$data[$id_field] = $data[$id_field.'_'.$type];

		$ids_columns = collect($data)->keys()->filter(function($item) use ($id_field) {
			return Str::contains($item, $id_field.'_');
		});
		foreach ($ids_columns as $column) {
			unset($data[$column]);
		}
		return $data;
	}

}
