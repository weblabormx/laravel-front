<?php

namespace WeblaborMx\Front\Inputs;

class BelongsToMany extends HasMany
{
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
		$this->resource = $resource;
		return $this;
	}
}
