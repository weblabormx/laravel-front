<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;
use Opis\Closure\SerializableClosure;
use WeblaborMx\Front\Traits\InputRelationship;

class BelongsTo extends Input
{
	use InputRelationship;

	public $relation;
	public $relation_front;
	public $searchable = false;
	public $search_field;

	public function __construct($title, $column = null, $extra = null, $source = null)
	{
		$this->column = $column;
		$this->extra = $extra;
		$this->source = $source;
		$this->relation = Str::snake($this->column);

		if(!isset($this->extra)) {
			$front = Str::singular($this->relation);
			$front = ucfirst(Str::camel($front));
			$this->extra = $title;
		}

		$this->model_name = $this->extra;
		$class = 'App\Front\\'.$this->model_name;
		$this->relation_front = new $class($this->source);
		$class = $this->relation_front->getModel();
		$this->title = $this->relation_front->label;

		$this->load();
	}

	public function setResource($resource)
	{
		$relation = $this->relation;
		$class = $resource->getModel();
		$model = new $class;
		if(method_exists($model, $relation)) {
			$relation_function = $model->$relation();
			$this->column = $relation_function->getForeignKeyName();
		} else {
			$this->column = str_replace(' ', '_', strtolower($this->column));
		}
		return parent::setResource($resource);
	}

	public function getValue($object)
	{
		$relation = $this->relation;
		if(!is_object($object->$relation)) {
			return '--';
		}

		$title_field = $this->search_field ?? $this->relation_front->search_title;
		$value = $object->$relation->$title_field;
		if(!isset($this->link)) {
			$this->link = $this->relation_front->base_url.'/'.$object->$relation->getKey();	
		}
		if(!isset($value)) {
			return '--';
		}
		return $value;
	}

	public function form()
	{
		$relation_front = $this->relation_front;

		// If searchable make a way to search
		if($this->searchable) {
			$title_field = $this->search_field ?? $this->relation_front->search_title;
			$value = isset($this->default_value) ? $this->default_value : \Form::getValueAttribute($this->column);
			$title = null;
			if(isset($value)) {
				$object = $relation_front->globalIndexQuery()->find($value);
				$title = isset($object) ? $object->$title_field : null;
			}
			$serialized = null;
			if(isset($this->filter_query)) {
				$filter_query = $this->filter_query;
				$wrapper = new SerializableClosure($filter_query);
				$serialized = serialize($wrapper);
				$serialized = json_encode($serialized);
			}
			return Autocomplete::make($this->title, $this->column)->setUrl($relation_front->base_url.'/search?filter_query='.$serialized)->setText($title)->default($this->default_value)->form();
		}

		$model = $this->relation_front->getModel();
		$model = new $model;

		if(isset($this->force_query)) {
			$force_query = $this->force_query;
			$query = $force_query($model);
		} else {
			$query = $this->relation_front->globalIndexQuery();	
		}

		if(isset($this->filter_query)) {
			$filter_query = $this->filter_query;
			$query = $filter_query($query);
		}
		
		$options = $query->get();
		if(isset($this->filter_collection)) {
			$filter_collection = $this->filter_collection;
			$options = $filter_collection($options);
		}

		$title = $this->search_field ?? $this->relation_front->search_title;
		$options = $options->pluck($title, $model->getKeyName());
		$select = Select::make($this->title, $this->column)->options($options)->default($this->default_value);
		return $select->form();
	}

	public function searchable($searchable = true)
	{
		$this->searchable = $searchable;
		return $this;
	}

	public function setSearchField($field)
	{
		$this->search_field = $field;
		return $this;
	}
}
