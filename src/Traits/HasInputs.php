<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Components\Panel;
use Illuminate\Support\Str;

trait HasInputs
{
	private $relations = ['HasMany', 'MorphMany', 'MorphToMany'];

	/* 
	 * Functions
	 */

	public function fields()
    {
        return [];
    }

	private function filterFields($where, $flatten = false)
	{
		$sum = [];
		if($where=='index' || $flatten) {
			$sum = collect($this->fields())->flatten()->filter(function($item) {
				return is_object($item) && class_basename(get_class($item)) == 'Panel';
			})->filter(function($item) use ($where) {
				$field = 'show_on_'.$where;
				return $item->$field && $item->show;
			})->map(function($item) {
				return $item->column;
			})->flatten()->filter(function($item) use ($where) {
				$field = 'show_on_'.$where;
				return $item->$field && $item->show;
			})->map(function($item) use ($where) {
				return $item->setResource($this)->setSource($where);
			});
		}
		$return = collect($this->fields())->flatten()->filter(function($item) {
			return isset($item);
		})->filter(function($item) use ($where) {
			$field = 'show_on_'.$where;
			return $item->$field && $item->show;
		})->map(function($item) use ($where) {
			return $item->setResource($this)->setSource($where);
		})->map(function($item) use ($where) {
			if(class_basename(get_class($item)) != 'Panel') {
				return $item;
			}
			$item->column = collect($item->column)->flatten()->filter(function($item) use ($where) {
				$field = 'show_on_'.$where;
				return $item->$field && $item->show;
			})->map(function($item) use ($where) {
				return $item->setResource($this)->setSource($where);
			})->values()->toArray();
			return $item;
		})->filter(function($item) {
			if(!isset($this->hide_columns)) {
				return true;
			}
			$columns = $this->hide_columns;
			if(!is_array($columns)) {
				$columns = [$columns];
			}
			return !collect($columns)->contains($item->column);
		});
		$return = collect($return)->merge($sum);
		return $return;
	}

	public function allFields()
	{
		return $this->filterFields('show');
	}

	public function indexFields()
	{
		return $this->filterFields('index')->filter(function($item) {
			return $item->is_input;
		});
	}

	private function addAtLeastOnePanel($type)
	{
		$fields = $this->filterFields($type)->filter(function($item) {
			return !Str::contains(class_basename(get_class($item)), $this->relations);
		});
		$components = $fields->filter(function($item) {
			return class_basename(get_class($item)) == 'Panel';
		})->filter(function($item) {
			return $item->fields()->count() > 0;
		});
		$fields = $fields->filter(function($item) {
			return class_basename(get_class($item)) != 'Panel';
		});
		if($fields->count() > 0) {
			$components[-1] = Panel::make('', $fields);	
		}
		return $components->sortKeys()->values();
	}

	public function showPanels()
	{
		return $this->addAtLeastOnePanel('show');
	}

	public function showRelations()
	{
		return $this->filterFields('show')->filter(function($item) {
			return Str::contains(class_basename(get_class($item)), $this->relations);
		});
	}

	public function editPanels()
	{
		return $this->addAtLeastOnePanel('edit');
	}

	public function createPanels()
	{
		return $this->addAtLeastOnePanel('create');
	}
}
