<?php

namespace WeblaborMx\Front\Traits;

trait HasActions
{
	public function actions()
    {
        return [];
    }

    public function indexActions()
    {
        return [];
    }

    public function getIndexActions($all = false)
    {
    	$actions = collect($this->indexActions());
    	if($all) {
    		$actions = collect($this->fields())->filter(function($item) {
	    		return isset($item->actions) && count($item->actions)>0;
	    	})->pluck('actions')->flatten(1)->merge($actions);
    	}
    	return $actions->map(function($item) {
    		return $item->addData($this->data);
    	});
    }

    public function getActions($all = false)
    {
    	$actions = collect($this->actions());
    	if($all) {
    		$actions = collect($this->fields())->filter(function($item) {
	    		return isset($item->actions) && count($item->actions)>0;
	    	})->pluck('actions')->flatten(1)->merge($actions);
    	}
    	return $actions->filter(function($item) use ($all) {
    		if($all) {
    			return true;
    		}
    		return $item->show;
    	})->map(function($item) {
    		return $item->addData($this->data);
    	});
    }
}
