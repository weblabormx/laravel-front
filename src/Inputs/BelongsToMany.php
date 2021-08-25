<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\InputWithActions;
use WeblaborMx\Front\Traits\InputWithLinks;
use WeblaborMx\Front\Traits\InputRelationship;

class BelongsToMany extends Input
{
	use InputWithActions, InputWithLinks, InputRelationship;
	
	public $is_input = false;
	public $show_on_edit = false;
	public $show_on_create = false;
	public $show_on_index = false;

	public function __construct($front, $title = null, $column = null, $source = null)
	{
		$this->front = getFront($front, $source);
		$this->column = $column;
		$this->source = $source;
		if(!is_null($title)) {
			$this->title = $title;
			$this->relationship = Str::snake(Str::plural($this->title));
		} else {
			$this->title = $this->front->plural_label;
			$this->relationship = Str::snake(Str::plural(class_basename(get_class($this->front))));
		}
		
		$this->massive_edit_link = '';
	}

	public static function make($title = null, $column = null, $extra = null) 
	{
		$source = session('source');
		return new static($title, $column, $extra, $source);	
	}

	public function setResource($resource)
	{
		// Get column name
		if(is_null($this->column)) {
			$relation = $this->relationship;
			$class = $resource->getModel();
			$model = new $class;
			$relation_function = $model->$relation();
			$this->column = $relation_function->getForeignPivotKeyName();
		}

		$base_url = $this->getBaseUrl($resource, $relation_function);
		return parent::setResource($resource);
	}

	public function getValue($object)
	{
		// Get results
		$pagination_name = $this->relationship.'_page';
		$result = $this->getResults($object);
		if(get_class($result) != 'Illuminate\Support\Collection') {
			$result = $result->paginate($this->front->pagination, ['*'], $pagination_name);
		}

		$front = $this->front;
		$edit_link = $this->edit_link;
		$show_link = $this->show_link;

		return view('front::crud.partial-index', compact('result', 'front', 'pagination_name', 'edit_link', 'show_link'))->render();
	}

	public function getResults($object)
	{
		// Get objects
		$relationship = $this->relationship;
		$objects = $object->$relationship()->with($this->with);;
		return $objects;
	}

	/*
	 * Shared functions
	 */

	public function getBaseUrl($resource, $relation_function)
	{
		return $this->column.'='.$resource->object->getKey();
	}
}
