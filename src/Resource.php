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
use WeblaborMx\Front\Traits\ResourceHelpers;
use WeblaborMx\Front\Traits\IsValidated;

abstract class Resource
{
	use HasInputs, HasActions, HasLinks, HasBreadcrumbs, HasFilters, Sourceable, HasCards, HasLenses, ResourceHelpers, IsValidated;

	public $data;
	public $title = 'name';
    public $search_title;
	public $label;
	public $base_url;
	public $ignore_if_null = [];
	public $show_title = true;
    public $show_create_button_on_index = true;
    public $pagination = 50;
    public $layout;
    public $functions_values = [];
    public $actions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    public $index_views = [];

	public function __construct($source = null)
	{
		if(!isset($this->label)) {
            $label = trim(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', class_basename(get_class($this))));
			$this->label = $label;
		}

        if(!isset($this->plural_label)) {
            $this->plural_label = __(Str::plural($label ?? $this->label));
        }
        $this->label = __($this->label);

		$this->setSource($source);
        if(!isset($this->view_title)) {
            $this->view_title = $this->title;
        }
        if(!isset($this->search_title)) {
            $this->search_title = $this->title;
        }
        if(!isset($this->index_views) || (is_array($this->index_views) && count($this->index_views)==0)) {
            $this->index_views = [
                'normal' => [
                    'icon' => 'fa fa-th-list',
                    'title' => 'Normal',
                    'view' => 'front::crud.partial-index'
                ]
            ];
        }
        if(isset($this->lense_title)) {
            $this->label = $this->label.' '.$this->lense_title;
            $this->plural_label = $this->plural_label.' '.$this->lense_title;
        }
        $this->load();
	}

    /* 
     * Functions that can be modified
     */

    // Function that is called after the constructor is called

    public function load()
    {
        //
    }

    // Modify how to return results

	public function indexQuery($query)
	{
		return $query->latest();
	}

    // To execute when seeing a show view

    public function show($object)
    {
        //
    }

    // To execute after storing an object

    public function store($object, $request)
    {
        //
    }

    public function update($object, $request)
    {
        //
    }

    public function destroy($object)
    {
        //
    }

    public function index()
    {
        //
    }

    public function indexResult($result)
    {
        return $result;
    }

    public function create($data)
    {
        $model = $this->getModel();
        return $model::create($data);
    }

    public function cacheFor()
    {
        return false;
    }
    
    public function createRedirectionUrl()
    {
        return $this->getBaseUrl();
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
        try {
            $filters = $this->getFilters();
        } catch (\Exception $e) {
            return $query;
        }
		foreach ($filters as $filter) {
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
			return isset($item) && strlen($item);
		});

		// If we dont have any value dont do anything
		if($filters->count() <= 0) {
			return;
		}
        
		// Generate the url to be redirected
        $filters['is_redirect'] = true;
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

        // Get fields 
        $fields = collect($this->filterFields($this->source=='store' ? 'create' : 'edit', true))->filter(function($item) {
            return $item->shouldBeShown();
        });

        $this->makeValidation($fields, $data);
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

    public function processDataAfterValidation($inputs)
    {
        // Get fields processing
        $fields = $this->filterFields($this->source=='update' ? 'edit' : 'create', true);
        $fields->filter(function($item) use ($inputs) {
            return $item->is_input;
        })->each(function($item) use (&$inputs) {
            $inputs = $item->processDataAfterValidation($inputs);
        });
        return $inputs;
    }

    // If the inputs have a removeAction is executed before its really removed

    public function processRemoves($object)
    {
         // Get fields processing
        $fields = $this->filterFields('edit', true);

        // Execute removeAction function for every input
        $fields->each(function($item) use ($object) {
            $item->removeAction($object);
        });
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
    
    public function getBaseUrl()
    {
        $base_url = $this->base_url;

        // Get all route parameters
        $parameters = request()->route()->parameters();

        // Find which values are required on base_url
        preg_match_all('#\{(.*?)\}#', $this->base_url, $match);
        $results = $match[1];

        foreach ($results as $result) {
            $result_explode = explode(':', $result);

            // if value on base url doesnt exist on parameters so ignore
            if(!isset($parameters[$result_explode[0]])) {
                continue;
            }

            // Get value on parameters
            $value = $parameters[$result_explode[0]];

            // If there isnt any field selected
            if(!isset($result_explode[1])) {
                $base_url = str_replace('{'.$result.'}', $value, $base_url);
            } else {
                $column = $result_explode[1];
                $base_url = str_replace('{'.$result.'}', $value->$column, $base_url);
            }
        }
        return $base_url;
    }

    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
        return $this;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function setPluralLabel($plural_label)
    {
        $this->plural_label = $plural_label;
        return $this;
    }

    public function getCurrentViewName()
    {
        $name = Str::snake(class_basename(get_class($this)));
        $name .= '_view';
        return request()->$name ?? collect($this->index_views)->keys()->first();
    }

    public function getCurrentView()
    {
        $current_view_name = $this->getCurrentViewName();
        $view = collect($this->index_views)->filter(function($item, $key) use ($current_view_name) {
            return $key==$current_view_name;
        })->first();
        return $view['view'];
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
