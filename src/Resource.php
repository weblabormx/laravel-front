<?php

namespace WeblaborMx\Front;

use Exception;
use Illuminate\Routing\Route;
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
use WeblaborMx\Front\Traits\HasPermissions;
use WeblaborMx\Front\Traits\HasMassiveEditions;
use Illuminate\Support\Arr;
use WeblaborMx\Front\Facades\Front;

abstract class Resource
{
    use HasInputs;
    use HasActions;
    use HasLinks;
    use HasBreadcrumbs;
    use HasFilters;
    use Sourceable;
    use HasCards;
    use HasLenses;
    use ResourceHelpers;
    use IsValidated;
    use HasPermissions;
    use HasMassiveEditions;

    public $data;
    public $model;
    public $search_title;
    public $label;
    public $base_url;
    public $layout;
    public $view_title;
    public $plural_label;
    public $object;
    public $hide_columns;
    public $title = 'name';
    public $ignore_if_null = [];
    public $show_title = true;
    public $show_create_button_on_index = true;
    public $pagination = 50;
    public $search_limit = 10;
    public $functions_values = [];
    public $actions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    public $index_views = [];
    public $cache = ['indexQuery', 'indexResult'];
    public $related_object;
    public $enable_massive_edition = false;

    public function __construct($source = null)
    {
        if (!isset($this->label)) {
            $base = Str::contains(get_class($this), 'Lense') ? get_parent_class($this) : get_class($this);
            $base = class_basename($base);
            $label = trim(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $base));
            $this->label = $label;
        }

        if (!isset($this->plural_label)) {
            $this->plural_label = __(Str::plural($label ?? $this->label));
        }
        $this->label = __($this->label);

        $this->setSource($source);
        if (!isset($this->view_title)) {
            $this->view_title = $this->title;
        }
        if (!isset($this->search_title)) {
            $this->search_title = $this->title;
        }
        if (!isset($this->index_views) || (is_array($this->index_views) && count($this->index_views) == 0)) {
            $this->index_views = [
                'normal' => [
                    'icon' => 'fa fa-th-list',
                    'title' => 'Normal',
                    'view' => 'front::crud.partial-index'
                ]
            ];
        }
        if (is_array($this->indexViews())) {
            $this->index_views = $this->indexViews();
        }
        if (is_numeric($this->pagination())) {
            $this->pagination = $this->pagination();
        }
        $this->load();
    }

    public function route(string $source = null): ?Route
    {
        return Front::routeOf($this, $source);
    }

    /* ==============
     * Hooks
     ================*/

    /**
     * Ran after authorization, but before
     * running any action for the resource.
     * @return mixed Return Response to hijack the request.
     */
    public function beforeRequest()
    {
        //
    }

    // Functions to modify the attribute on traits

    public function indexViews()
    {
        //
    }

    public function pagination()
    {
        //
    }

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

    // Modify the results gotten on the query

    public function indexResult($result)
    {
        return $result;
    }

    // To execute when seeing the index view

    public function index()
    {
        //
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

    // To edit the object before saving it

    public function processDataBeforeSaving($data)
    {
        return $data;
    }

    // To execute before updating an object

    public function beforeUpdate($object, $request)
    {
        //
    }

    // To execute after updating an object

    public function update($object, $request)
    {
        //
    }

    /**
     * To execute before destroying an object.
     *
     * Return `false` to prevent the destroy process.
     *
     * @return false|null
     */
    public function destroy($object)
    {
        //
    }

    // To execute after massive is done

    public function afterMassive($objects)
    {
        //
    }

    // How to create the object

    public function create($data)
    {
        $model = $this->getModel();
        return $model::create($data);
    }

    // Set time for cache results

    public function cacheFor()
    {
        return false;
    }

    // Change url for redirection after a creation is done

    public function createRedirectionUrl($object)
    {
        return $this->getBaseUrl();
    }

    // Change url for redirection after a delete is done

    public function removeRedirectionUrl()
    {
        return $this->getRelatedLink() ?? $this->getBaseUrl();
    }

    /*
     * Hidden functions
     */

    public function globalIndexQuery($query = null)
    {
        $class = $this->getModel();
        if (is_null($query)) {
            $query = new $class();
        }

        // Get filters
        try {
            $filters = $this->getFilters();
        } catch (\Exception $e) {
            return $query;
        }

        // Execute before
        foreach ($filters as $filter) {
            $field = $filter->slug;
            $field = str_replace('[', '', $field);
            $field = str_replace(']', '', $field);
            if (!request()->filled($field) || !$filter->execute_before) {
                continue;
            }
            $filter->setResource($this);
            $value = request()->$field;
            $query = $filter->apply($query, $value);
        }

        $query = $this->indexQuery($query);

        // Execute after
        foreach ($filters as $filter) {
            $field = $filter->slug;
            $field = str_replace('[', '', $field);
            $field = str_replace(']', '', $field);
            if (!request()->filled($field) || $filter->execute_before) {
                continue;
            }
            $filter->setResource($this);
            $value = request()->$field;
            $query = $filter->apply($query, $value);
        }

        // Detect if the indexQuery value is not the model empty
        if ($class == get_class($query) && is_null($query->getKey())) {
            $query = $query->oldest();
        }

        return $query;
    }

    public function sourceIsForm()
    {
        return $this->source != 'index' && $this->source != 'show';
    }

    public function redirects($is_first = true)
    {
        if (request()->filled('is_redirect') && $is_first) {
            return;
        }
        if (request()->filled('dont_redirect')) {
            return;
        }

        $try = session('resource.redirect_tries', 0);
        $try = $is_first ? 0 : $try + 1;

        $exist_filter_value = false;

        // Get all the filters variables with their default values
        $filters = collect($this->filters())->mapWithKeys(function ($filter) use ($try, &$exist_filter_value) {
            // Default value
            $default = $filter->setResource($this)->default();
            if (is_array($default) && isset($default[$try])) {
                $default = $default[$try];
                $exist_filter_value = true;
            } elseif (is_array($default) && !isset($default[$try])) {
                $default = $default[0];
            }
            return [$filter->slug => $default ?? null];
        })->filter(function ($item) {
            return isset($item) && strlen($item);
        });

        $clean_filters = $filters->mapWithKeys(function ($item, $key) {
            $key = str_replace('[', '', $key);
            $key = str_replace(']', '', $key);
            return [$key => $item];
        });

        $filters_with_default_values_are_set = $clean_filters->keys()->intersect(collect(request()->all())->keys())->count() == $filters->count();

        // Only will acess if the url doesnt have the required variables
        if ($filters_with_default_values_are_set && !request()->filled('is_redirect')) {
            return;
        }

        session(['resource.redirect_tries' => $try]);

        // Respect currect request data
        $filters = collect(request()->all())->merge($filters);
        if (!$exist_filter_value) {
            $filters['dont_redirect'] = true;
        }

        // Generate the url to be redirected
        $filters['is_redirect'] = true;
        $url = request()->url();
        $url .= '?' . http_build_query($filters->toArray());
        return $url;
    }

    public function validate($data)
    {
        // Just execute on edit or create
        if ($this->source != 'update' && $this->source != 'store') {
            return;
        }

        $this->makeValidation($data);
        return $this;
    }

    // If the inputs have a removeAction is executed before its really removed

    public function processRemoves($object)
    {
        // Get fields processing
        $fields = $this->filterFields('edit', true);

        // Execute removeAction function for every input
        $fields->each(function ($item) use ($object) {
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

        if (isset($model)) {
            return $model;
        }

        if (isset($this->object) && is_object($this->object)) {
            return get_class($this->object);
        }

        $class = $this::class;
        throw new Exception("Front '{$class}' resource model couldn't be found");
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
        $parameters = Arr::dot(collect(request()->route()->parameters())->toArray());

        // Find which values are required on base_url
        preg_match_all('#\{(.*?)\}#', $this->base_url, $match);
        $results = $match[1];

        foreach ($results as $result) {
            $result_explode = explode(':', $result);

            // if value on base url doesnt exist on parameters so ignore
            if (!isset($parameters[$result_explode[0]])) {
                continue;
            }

            // Get value on parameters
            $value = $parameters[$result_explode[0]];

            // If there isnt any field selected
            $column = $result_explode[1] ?? null;
            if (!isset($result_explode[1]) || !isset($value->$column)) {
                $base_url = str_replace('{' . $result . '}', $value, $base_url);
            } else {
                $base_url = str_replace('{' . $result . '}', $value->$column, $base_url);
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

    public function getCurrentViewRequestName()
    {
        $name = Str::snake(class_basename(get_class($this)));
        $name .= '_view';
        return $name;
    }

    public function getCurrentViewName()
    {
        $name = $this->getCurrentViewRequestName();
        return request()->$name ?? collect($this->index_views)->keys()->first();
    }

    public function getCurrentView()
    {
        $current_view_name = $this->getCurrentViewName();
        $view = collect($this->index_views)->filter(function ($item, $key) use ($current_view_name) {
            return $key == $current_view_name;
        })->first();
        return $view['view'];
    }

    public function setRelatedObject($related_object)
    {
        $this->related_object = $related_object;
        return $this;
    }

    public function getTitle()
    {
        $field = $this->title;
        return $this->object?->$field;
    }

    /*
     * Private functions
     */
    private function getRelatedLink()
    {
        $relatedInput = $this->related_object;
        $relatedResource = $relatedInput?->resource;

        if (is_null($relatedInput) || is_null($relatedResource)) {
            return null;
        }

        $helper = $this->getActionsHelper($relatedResource->object, $relatedResource->getBaseUrl(), null, null);
        return $helper->showUrl();
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
