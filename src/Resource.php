<?php

namespace WeblaborMx\Front;

use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\HasInputs;
use WeblaborMx\Front\Traits\HasActions;
use WeblaborMx\Front\Traits\HasLinks;
use WeblaborMx\Front\Traits\HasBreadcrumbs;
use WeblaborMx\Front\Traits\HasFilters;
use WeblaborMx\Front\Traits\Sourceable;
use WeblaborMx\Front\Traits\HasCards;
use WeblaborMx\Front\Traits\HasLenses;

abstract class Resource
{
	use HasInputs, HasActions, HasLinks, HasBreadcrumbs, HasFilters, Sourceable, HasCards, HasLenses;

	public $data;
	public $title = 'name';
	public $label;
	public $base_url;
	public $ignore_if_null = [];
	public $show_title = true;
    public $show_create_button_on_index = true;
    public $pagination = 50;
    public $layout = 'layouts.app';

	public function __construct($source = null)
	{
		if(!isset($this->label)) {
			$this->label = trim(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', class_basename(get_class($this))));
		}
        if(!isset($this->plural_label)) {
            $this->plural_label = Str::plural($this->label);
        }
		$this->setSource($source);
        if(!isset($this->view_title)) {
            $this->view_title = $this->title;
        }
        $this->load();
	}

    public function load()
    {
        //
    }

	/* 
	 * Functions that can be modified
	 */

	public function indexQuery($query)
	{
		return $query->latest();
	}

    public function show($object, $extra = [])
    {
        // Do nothing
    }

    public function store($object, $request, $extra = [])
    {
        // Do nothing
    }

    public function update($object, $request, $extra = [])
    {
        // Do nothing
    }

    public function destroy($object, $extra = [])
    {
        // Do nothing
    }

	/* 
	 * Hidden functions
	 */

	public function globalIndexQuery($query = null)
	{
        $class = $this->getModel();
        if(is_null($query)) {
            $query = new $class;
        }
            
		$query = $this->indexQuery($query);

        // Detect if the indexQuery value is not the model empty
        if($class == get_class($query) && is_null($query->getKey()) ) {
            $query = $query->oldest();
        }

        // Execute filters
		foreach ($this->filters() as $filter) {
			$field = $filter->slug;
			if(!request()->filled($field)) {
				continue;
			}
			$filter->setResource($this);
			$value = request()->$field;
			$query = $filter->apply($query, $value);
		}
		return $query;
	}
	
	public function sourceIsForm()
	{
		return $this->source!='index' && $this->source!='show';
	}

    public function redirects($is_first = true)
    {
    	if(request()->filled('is_redirect') && $is_first) {
    		return;
    	}

    	// Only will acess if the url doesnt have any variable sent
    	if(count(request()->all())>0 && !request()->filled('is_redirect')) {
    		return;
    	}

    	$try = \Cache::get('resource.redirect_tries');
    	$try = $is_first ? 0 : $try+1;
    	\Cache::put('resource.redirect_tries', $try, now()->addSeconds(10));

    	// Get all the filters variables with their default values
    	$filters = collect($this->filters())->mapWithKeys(function($filter) use ($try) {
    		// Default value
	    	$default = $filter->setResource($this)->default();
	    	if(is_array($default)) {
	    		$default = $default[$try] ?? null;
	    	}
    		return [$filter->slug => $default ?? null];
		})->filter(function($item) {
			return isset($item);
		});
		
		$filters['is_redirect'] = true;
		// If we dont have any value dont do anything
		if($filters->count() <= 0) {
			return;
		}
		// Generate the url to be redirected
		$url = request()->url();
		$url .= '?'.http_build_query($filters->toArray());
		return $url;
    }

    public function validate($data)
    {
        // Just execute on edit or create
    	if($this->source != 'update' && $this->source != 'store') {
    		return;
    	}
        // Get rules on inputs of the resource
    	$rules = collect($this->filterFields($this->source=='store' ? 'create' : 'edit', true))->filter(function($item) {
    		return count($item->getRules($this->source))>0 && $item->shouldBeShown();
    	})->map(function($item) {
    		return $item->setResource($this);
    	})->mapWithKeys(function($item) {
            $column = $item->column;
            $column = str_replace('[', '.', $column);
            $column = str_replace(']', '', $column);
    		return [$column => $item->getRules($this->source)];
    	})->toArray();

        // Execute validator function on fields
        collect($this->filterFields($this->source=='store' ? 'create' : 'edit', true))->map(function($item) {
            return $item->setResource($this);
        })->each(function($item) use ($data) {
            return $item->validate($data);
        });

        // Validate all the rules
        $attributes = collect($this->filterFields($this->source=='store' ? 'create' : 'edit', true))->filter(function($item) {
            return isset($item->column) && isset($item->title) && is_string($item->column) && is_string($item->title);
        })->mapWithKeys(function($item) {
            return [$item->column => $item->title];
        })->toArray();
        \Validator::make($data, $rules, [], $attributes)->validate();
    	return $this;
    }

    public function processData($inputs)
    {
        // Remove from the inputs fields marked as null
    	foreach($this->ignore_if_null as $input) {
    		if(is_null($inputs[$input])) {
    			unset($inputs[$input]);
    		}
    	}

        // Remove redirect url helper
        unset($inputs['redirect_url']);

        // Get fields processing
        $fields = $this->filterFields($this->source=='update' ? 'edit' : 'create', true);

        // Remove autocomplete helper input
        $autocomplete_fields = $fields->filter(function($item) {
            return isset($item->searchable) && $item->searchable;
        })->map(function($item) {
            return $item->column.'ce';
        })->values()->each(function($item) use (&$inputs) {
            unset($inputs[$item]);
        });

        $fields->filter(function($item) use ($inputs) {
            return $item->is_input;
        })->each(function($item) use (&$inputs) {
            $inputs = $item->processData($inputs);
        });
    	return $inputs;
    }

    /* 
	 * Setters and getters
	 */

    public function setObject($object)
    {
    	$this->object = $object;
    	return $this;
    }

    public function setModel($model)
    {
    	$this->model = $model;
    	return $this;
    }

    public function getModel()
    {
    	$model = $this->model ?? null;
    	if(isset($model)) {
            return $model;
        }
        if(isset($this->object) && is_object($this->object)) {
            return get_class($this->object);
        }
        $return = 'App\\'.class_basename(get_class($this));
        if(class_exists($return)) {
            return $return;
        }
        return class_basename(get_class($this));
    }

    public function addData($data)
	{
		$this->data = $data;
		return $this;
	}

    public function hideColumns($hide_columns)
    {
        $this->hide_columns = $hide_columns;
        return $this;
    }
    
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
        return $this;
    }

    /* 
	 * Special functions
	 */

    public function __get($name)
    {
        if (isset($this->object) && isset($this->object->$name)) {
            return $this->object->$name;
        }
    }

    public function __isset($name)
    {
        if (isset($this->object) && isset($this->object->$name)) {
            return $this->object->$name;
        }
    }

}
