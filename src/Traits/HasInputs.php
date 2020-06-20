<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Components\Panel;
use Illuminate\Support\Str;

trait HasInputs
{
	private $relations = ['HasMany', 'MorphMany', 'MorphToMany', 'BelongsToMany'];
	public $fields_function = 'fields';

	/* 
	 * Functions
	 */

	public function fields()
    {
        return [];
    }

    public function setFields($fields, $function = 'fields')
    {
    	$this->functions_values[$function] = $fields;
    	return $this;
    }

    public function getFields()
    {
    	$fields_function = $this->fields_function;
    	if(isset($this->functions_values[$fields_function])) {
    		return $this->functions_values[$fields_function];
    	}
    	// Add priority to this
    	if($fields_function!='fields') {
    		return $this->$fields_function();	
    	}
    	// Dynamic fields name
    	if(method_exists($this, 'getCurrentViewName') && $this->getFieldsOnView()!=false) {
    		return $this->getFieldsOnView();
    	}
	    	
    	return $this->$fields_function();
    }

    private function getFieldsOnView()
    {
    	$view = $this->getCurrentViewName();
    	if($view!='normal') {
    		$function = 'fieldsOn'.ucfirst(Str::camel($view)).'View';
    		$exists = method_exists($this, $function);
    		if($exists) {
    			return $this->$function();
    		}
    	}
    	return false;
    }

    // This need to be improved
	private function filterFields($where, $flatten = false)
	{
		$sum = [];
		if($where=='index' || $flatten) {
			$sum = collect($this->getFields())->flatten()->filter(function($item) {
				return is_object($item) && class_basename(get_class($item)) == 'Panel';
			})->filter(function($item) use ($where) {
				$field = 'show_on_'.$where;
				return $item->$field && $item->shouldBeShown();
			})->map(function($item) {
				return $item->column;
			})->flatten()->filter(function($item) use ($where) {
				$field = 'show_on_'.$where;
				return $item->$field && $item->shouldBeShown();
			})->filter(function($item) {
				if(!isset($this->hide_columns) || is_null($item->column)) {
					return true;
				}
				$columns = $this->hide_columns;
				if(!is_array($columns)) {
					$columns = [$columns];
				}
				return !collect($columns)->contains($item->column);
			})->map(function($item) use ($where) {
				return $item->setResource($this)->setSource($where);
			});
		}
		$return = collect($this->getFields())->flatten()->filter(function($item) {
			return isset($item);
		})->filter(function($item) use ($where) {
			$field = 'show_on_'.$where;
			return $item->$field && $item->shouldBeShown();
		})->map(function($item) use ($where) {
			return $item->setResource($this)->setSource($where);
		})->map(function($item) use ($where) {
			if(class_basename(get_class($item)) != 'Panel') {
				return $item;
			}
			$item->column = collect($item->column)->flatten()->filter(function($item) use ($where) {
				$field = 'show_on_'.$where;
				return $item->$field && $item->shouldBeShown();
			})->map(function($item) use ($where) {
				return $item->setResource($this)->setSource($where);
			})->values()->toArray();
			return $item;
		})->filter(function($item) {
			if(!isset($this->hide_columns) || is_null($item->column)) {
				return true;
			}
			$columns = $this->hide_columns;
			if(!is_array($columns)) {
				$columns = [$columns];
			}
			return !in_array($item->column, $columns);
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
		// Get all fields that are not relationships
		$fields = $this->filterFields($type)->filter(function($item) {
			return !Str::contains(class_basename(get_class($item)), $this->relations);
		});

		// Get components or elements that dont need to have a panel
		$components = $fields->filter(function($item) {
			return class_basename(get_class($item)) == 'Panel' || !$item->needs_to_be_on_panel;
		})->filter(function($item) {
			return class_basename(get_class($item)) != 'Panel' || $item->fields()->count() > 0;
		});

		// Get other fields that were not gotten on $components
		$new_panels = [];
		$last_key = -99;
		$field_key = null;
		$fields = $fields->filter(function($item, $key) {
			return class_basename(get_class($item)) != 'Panel' && $item->needs_to_be_on_panel;
		})->each(function($item, $key) use (&$new_panels, &$last_key, &$field_key) {
			if($key-1!=$last_key) {
				$field_key = $key;
			}
			$new_panels[$field_key][] = $item;
			$last_key = $key;
		});

		// Create panels for this new_panels
		$new_panels = collect($new_panels)->map(function($item) {
			return Panel::make('', $item);
		});	
		return $components->union($new_panels)->sortKeys()->values();
	}

	public function showPanels()
	{
		return $this->addAtLeastOnePanel('show');
	}

	public function showRelations()
	{
		return $this->filterFields('show')->filter(function($item) {
			return Str::contains(class_basename(get_class($item)), $this->relations);
		})->values();
	}

	public function editPanels()
	{
		return $this->addAtLeastOnePanel('edit');
	}

	public function createPanels()
	{
		return $this->addAtLeastOnePanel('create');
	}

	public function changeFieldsFunction($fields_function)
	{
		$this->fields_function = $fields_function;
		return $this;
	}
}
