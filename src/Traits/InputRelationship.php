<?php

namespace WeblaborMx\Front\Traits;

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

	public function setCreateLink($function)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		$this->create_link_accessed = true;
		$this->create_link = $function($this->create_link);
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

	// Same that filtelQuery but now dont access to the globalIndexQuery

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
}	
