<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\InputWithActions;
use WeblaborMx\Front\Traits\InputWithLinks;
use WeblaborMx\Front\Traits\InputRelationship;
use Illuminate\Support\Facades\Gate;

class HasMany extends Input
{
	use InputWithActions, InputWithLinks, InputRelationship;
	
	public $is_input = false;
	public $show_on_edit = false;
	public $show_on_create = false;
	public $show_on_index = false;
	public $index_view = 'front::crud.partial-index';

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
		
		$this->create_link = $this->front->getBaseUrl().'/create';
		$this->show_before = Gate::allows('viewAny', $this->front->getModel()) && in_array('index', $this->front->actions);
		$this->masive_edit_link = '';
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

		$relation_front = str_replace(config('front.resources_folder').'\\', '', get_class($resource));

		// If any link has been set so add to select by default the relationhip
		if(!isset($this->create_link_accessed)) {
			$this->setCreateLink(function($link) use ($resource, $base_url, $relation_front) {
				return $link.'?'.$base_url.'&relation_front='.$relation_front.'&relation_id='.$resource->object->getKey().'&redirect_url='.$resource->getBaseUrl().'/'.$resource->object->getKey();
			});
		}

		// The same for edit
		if(!isset($this->edit_link_accessed)) {
			$this->setEditLink(function($link) use ($resource, $relation_front) {
				return $link.'?relation_front='.$relation_front.'&relation_id='.$resource->object->getKey();;
			});
		}

		// The same for show
		if(!isset($this->show_link_accessed)) {
			$this->setShowLink(function($link) use ($resource, $relation_front) {
				return $link.'?relation_front='.$relation_front.'&relation_id='.$resource->object->getKey();
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

		// Get results
		$pagination_name = $this->relationship;
		if(isset($this->title)) {
			$pagination_name = Str::slug($this->title, '_');
		}
		$pagination_name = $pagination_name.'_page';

		$result = $this->getResults($object);
		if(get_class($result) != 'Illuminate\Support\Collection') {
			$result = $result->paginate($this->front->pagination, ['*'], $pagination_name);
		}

		$front = $this->front;
		$edit_link = $this->edit_link;
		$show_link = $this->show_link;

		return view($this->index_view, compact('result', 'front', 'pagination_name', 'edit_link', 'show_link'))->render();
	}

	public function getResults($object)
	{
		// Set lense
		if(isset($this->lense)) {
   			$this->front = $this->front->getLense($this->lense);
   		}

		// Get objects
		$relationship = $this->relationship;
		$objects = $object->$relationship()->with($this->with);;

		// Force query if set
		if(isset($this->force_query)) {
			$force_query = $this->force_query;
			$objects = $force_query($objects);
		} else {
			$objects = $this->front->globalIndexQuery($objects);	
		}

		// Filter query
		if(isset($this->filter_query)) {
			$filter_query = $this->filter_query;
			$objects = $filter_query($objects);
		}
		return $objects;
	}

	public function setIndexView($value)
	{
		$this->index_view = $value;
		return $this;
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
