<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\InputWithActions;
use WeblaborMx\Front\Traits\InputWithLinks;
use WeblaborMx\Front\Traits\InputRelationship;

class HasMany extends Input
{
	use InputWithActions, InputWithLinks, InputRelationship;
	
	public $is_input = false;
	public $show_on_edit = false;
	public $show_on_create = false;
	public $show_on_index = false;

	public function __construct($front, $title = null, $column = null, $source = null)
	{
		$front = 'App\Front\\'.$front;
		$this->front = new $front($source);
		$this->column = $column;
		$this->source = $source;
		if(!is_null($title)) {
			$this->title = $title;
			$this->relationship = Str::snake(Str::plural($this->title));
		} else {
			$this->title = $this->front->plural_label;
			$this->relationship = Str::snake(Str::plural(class_basename(get_class($this->front))));
		}
		
		$this->create_link = $this->front->base_url.'/create';
		$this->show_before = \Auth::user()->can('viewAny', $this->front->getModel());
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
			$this->column = $relation_function->getForeignKeyName();
		}

		$base_url = $this->getBaseUrl($resource, $relation_function);
		
		// Hide column of the resource by default if there isnt any hide columns
		if(!isset($this->hide_columns)) {
			$this->front = $this->front->hideColumns($this->getColumnsToHide());
		}

		// If any link has been set so add to select by default the relationhip
		if(!isset($this->create_link_accessed)) {
			$this->setCreateLink(function($link) use ($resource, $base_url) {
				return $link.'?'.$base_url.'&relation_front='.class_basename(get_class($resource)).'&relation_id='.$resource->object->getKey().'&redirect_url='.$resource->base_url.'/'.$resource->object->getKey();
			});
		}

		// The same for edit
		if(!isset($this->edit_link_accessed)) {
			$this->setEditLink(function($link) use ($resource) {
				return $link.'?relation_front='.class_basename(get_class($resource)).'&relation_id='.$resource->object->getKey();;
			});
		}

		// The same for show
		if(!isset($this->show_link_accessed)) {
			$this->setShowLink(function($link) use ($resource) {
				return $link.'?relation_front='.class_basename(get_class($resource)).'&relation_id='.$resource->object->getKey();
			});
		}

		// Hide columns
		if(isset($this->hide_columns)) {
			$this->front = $this->front->hideColumns($this->hide_columns);
		}
		return parent::setResource($resource);
	}

	public function getValue($object)
	{
		// If any link has been set so add to select by default the relationhip
		if($this->create_link=='{key}/edit') {
			$this->setCreateLink(function($link) use ($resource) {
				return $link.'?'.$this->column.'='.$resource->object->getKey();
			});
		}

		$relation = $this->relationship;

		$pagination_name = $relation.'_page';
		$objects = $object->$relation()->with($this->with);
		$objects = $this->front->globalIndexQuery($objects);
		if(isset($this->filter_query)) {
			$filter_query = $this->filter_query;
			$objects = $filter_query($objects);
		}
		$objects = $objects->paginate(50, ['*'], $pagination_name);
		
		$front = $this->front;
		$edit_link = $this->edit_link;
		$show_link = $this->show_link;

		return view('front::crud.partial-index', compact('objects', 'front', 'pagination_name', 'edit_link', 'show_link'))->render();
	}

	/*
	 * Shared functions
	 */

	public function getBaseUrl($resource, $relation_function)
	{
		return $this->column.'='.$resource->object->getKey();
	}

	public function getColumnsToHide()
	{
		return $this->column;
	}
}
