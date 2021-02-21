<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Inputs\Text;
use WeblaborMx\Front\Inputs\Hidden;

trait InputRelationship
{
	public $create_link;
	public $edit_link = '{key}/edit';
	public $show_link;
	public $with = [];
	public $filter_query;
	public $filter_collection;
	public $force_query;
	public $lense;
	public $hide_columns;
	public $massive_class;
	public $show_massive = false;

	public function setCreateLink($function)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		$this->create_link_accessed = true;
		$this->create_link = $function($this->create_link);
		$this->masive_edit_link = $function($this->masive_edit_link);
		return $this;
	}

	public function setEditLink($function)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		$this->edit_link_accessed = true;
		$this->edit_link = $function($this->edit_link);
		return $this;
	}

	public function setShowLink($function)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		$this->show_link_accessed = true;
		$this->show_link = $function($this->show_link);
		return $this;
	}

	public function with($with)
	{
		$this->with = $with;
		return $this;
	}

	public function hideCreateButton()
	{
		$this->create_link = null;
		return $this;
	}

	public function setRequest($request)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		request()->request->add($request);
		return $this;
	}

	// Filter on the query

	public function filterQuery($query)
	{
		$this->filter_query = $query;
		return $this;
	}

	// Filter on the collection, after the get() 

	public function filterCollection($query)
	{
		$this->filter_collection = $query;
		return $this;
	}

	// Same that filterQuery but now dont access to the globalIndexQuery

	public function forceQuery($query)
	{
		$this->force_query = $query;
		return $this;
	}

	public function setLense($lense)
	{
		$this->lense = $lense;
		return $this;
	}

	public function hideColumns($hide_columns)
	{
		$this->hide_columns = $hide_columns;
		return $this;
	}

	public function setMassiveClass($class)
	{
		if(!is_null($class)) {
			$class = new $class;
		}
		$this->massive_class = $class;
		return $this;
	}

	public function enableMassive($value = true)
	{
		$this->show_massive = $value;
		return $this;
	}

	/*
	 * Helpers
	 */

	public function getMassiveForms()
	{
		$forms = [];
		if(!isset($this->massive_class) || (isset($this->massive_class) && $this->massive_class->new_rows_available)) {
			$forms[] = Text::make(__('New rows'), 'rows');
		}
        foreach(request()->except('rows') as $key => $value) {
        	$forms[] = Hidden::make($key)->setValue($value);
        }
        return $forms;
	}

	public function getTableHeadings($object)
	{
		// Always show ID column
		$headings = ['ID'];

		// Get front 
		$input_front = $this->front->setObject($this->getResults($object)->first());

		// Show fields that are on index and that can be edited
		$fields = $input_front->indexFields()->filter(function($item) {
			return $item->show_on_edit;
		});

		// Save titles to the result
        foreach($fields as $field) {
            $headings[] = $field->title;
        }

        // Return the headings
        return $headings;
	}

	public function getTableValues($object)
	{
		// Get front
		$input_front = $this->front->setObject($object);

		// Get id value
        $id = get_class($object)=='Illuminate\Database\Eloquent\Model' ? $object->getKey() : $object->id;

        // Start the result with the id result
		$values = [$id];

		// Show fields that are on index and that can be edited
		$fields = $input_front->indexFields()->filter(function($item) {
			return $item->show_on_edit;
		});

		// Save values to the result
        foreach($fields as $field) {
            $column = $field->column;
            $field = $field->setColumn($id.'['.$field->column.']')->default($object->$column, true);
            if(get_class($field)=='WeblaborMx\Front\Inputs\Number') {
            	$field = $field->size(50);
            }
            $values[] = $field->form($object);
        }

        // Return result
        return $values;
	}

	public function getExtraTableValues($object)
	{
		$result = [];

		// Show fields that are on index and that can be edited
		$fields = $this->front->indexFields()->filter(function($item) {
			return $item->show_on_edit;
		});

		if(!isset($this->massive_class) || (isset($this->massive_class) && $this->massive_class->new_rows_available)) {
            for($i = 0; $i < (request()->rows ?? 0); $i++) {
            	$values = [''];
            	foreach($fields as $field) {
                    $column = $field->column; 
                    $field = $field->setColumn('new'.$i.'['.$field->column.']');
                    $values[] = $field->form($object);
            	}
        		$result[] = $values;
            }
		}
        return $result;
	}

	public function getTableButtons()
	{
		$buttons = [];
		if(isset($this->massive_class)) {
            foreach($this->massive_class->buttons as $function => $title) {
            	$buttons[$function] = $title;
            }
        }
        $buttons[null] = '<i class="fa fa-save"></i> '.__('Save');
        return $buttons;
	}
}	
