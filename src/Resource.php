<?php

namespace WeblaborMx\Front;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use WeblaborMx\Front\{Facades\Front, Inputs\BelongsTo as BelongsToInput, Inputs\ID as IDInput, Traits};

abstract class Resource
{
    use Traits\HasInputs, Traits\HasActions, Traits\HasLinks, Traits\HasBreadcrumbs, Traits\HasFilters, Traits\Sourceable,
        Traits\HasLenses, Traits\ResourceHelpers, Traits\IsValidated, Traits\HasPermissions, Traits\HasMassiveEditions, Traits\HasCards;

    public $base_url, $data, $label, $layout, $model, $object, $plural_label, $related_object, $search_title, $view_title;
    public $functions_values = [], $hide_columns = [], $ignore_if_null = [], $index_views = [];
    public $actions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    public $cache = ['indexQuery', 'indexResult'];
    public $title = 'name';
    public $pagination = 50;
    public $search_limit = 10;
    public $show_create_button_on_index = true;
    public $show_title = true;
    public $enable_massive_edition = false;
    public $default_sort = null;
    public $default_sort_direction = 'desc';
    public $enable_index_sorting = true;
    public $enable_column_preferences = true;
    public $enable_export = true;
    public $enable_import = true;

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
                    'title' => __('Normal'),
                    'view' => 'front::crud.partial-index',
                ],
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

    public function route(?string $source = null): ?Route
    {
        return Front::routeOf($this, $source);
    }

    /* ==============
     * Hooks
     ================*/

    /**
     * Ran after authorization, but before
     * running any action for the resource.
     *
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
        return $query;
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

    // Change url for redirection after a update is done, if null we go back
    public function updateRedirectionUrl($object) {}

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
            $query = new $class;
        }

        // Get filters
        try {
            $filters = $this->getFilters();
        } catch (Exception $e) {
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

    public function applyIndexSorting($query)
    {
        $hasRequestedSort = request()->filled('sort');
        $sort = $hasRequestedSort ? request()->get('sort') : $this->defaultIndexSortColumn();

        if (($hasRequestedSort && !$this->enable_index_sorting) || !$sort) {
            return $query;
        }

        $field = $this->sortableIndexFields()->get($sort);

        if (is_null($field)) {
            if ($hasRequestedSort || !is_string($sort)) {
                return $query;
            }

            return $this->applyDefaultIndexSorting($query, $sort);
        }

        $defaultDirection = $this->default_sort_direction === 'asc' ? 'asc' : 'desc';
        $direction = request()->filled('sort')
            ? (request()->get('direction') === 'desc' ? 'desc' : 'asc')
            : $defaultDirection;

        if (is_callable($field->sort_callback)) {
            $callback = $field->sort_callback;

            return $callback($query, $direction, $field);
        }

        $query = is_callable([$query, 'reorder']) ? $query->reorder() : $query;

        $belongsToSortedQuery = !isset($field->sort_column)
            ? $this->applyBelongsToIndexSorting($query, $field, $direction)
            : null;

        if (!is_null($belongsToSortedQuery)) {
            $query = $belongsToSortedQuery;
        } else {
            $column = $field->sort_column ?? $field->column;

            if (!is_string($column)) {
                return $query;
            }

            $query = $query->orderBy($column, $direction);
        }

        $model = $this->getModel();
        $model = new $model;
        $key = $model->getQualifiedKeyName();

        return $query->orderBy($key);
    }

    public function defaultIndexSortColumn(): ?string
    {
        if (is_string($this->default_sort) && $this->default_sort !== '') {
            return $this->default_sort;
        }

        $model = $this->getModel();
        $model = new $model;

        return $model->getKeyName();
    }

    private function applyDefaultIndexSorting($query, string $sort)
    {
        $direction = $this->default_sort_direction === 'asc' ? 'asc' : 'desc';
        $query = is_callable([$query, 'reorder']) ? $query->reorder() : $query;

        return $query->orderBy($sort, $direction);
    }

    private function applyBelongsToIndexSorting($query, $field, string $direction)
    {
        $relation = $this->belongsToSortRelation($field);

        if (is_null($relation)) {
            return null;
        }

        $titleColumn = $this->belongsToSortTitleColumn($field, $relation);

        if (is_null($titleColumn)) {
            return null;
        }

        $relatedModel = $relation->getRelated();
        $relatedQuery = $relatedModel->newQuery()
            ->select($relatedModel->qualifyColumn($titleColumn))
            ->whereColumn(
                $relatedModel->qualifyColumn($relation->getOwnerKeyName()),
                $relation->getQualifiedForeignKeyName()
            )
            ->limit(1);

        return $query->orderBy($relatedQuery, $direction);
    }

    private function belongsToSortRelation($field): ?EloquentBelongsTo
    {
        $relationName = $this->belongsToSortRelationName($field);

        if (is_null($relationName)) {
            return null;
        }

        $model = $this->getModel();
        $model = new $model;

        if (!method_exists($model, $relationName)) {
            return null;
        }

        $relation = $model->$relationName();

        if (!$relation instanceof EloquentBelongsTo) {
            return null;
        }

        return $relation;
    }

    private function belongsToSortRelationName($field): ?string
    {
        if ($field instanceof BelongsToInput) {
            return $field->relation;
        }

        if (!is_string($field->column) || !Str::endsWith($field->column, '_id')) {
            return null;
        }

        return Str::camel(Str::beforeLast($field->column, '_id'));
    }

    private function belongsToSortTitleColumn($field, EloquentBelongsTo $relation): ?string
    {
        if ($field instanceof BelongsToInput) {
            $titleColumn = $field->search_field ?? $field->relation_front->search_title;

            return $this->belongsToSortColumnExists($relation, $titleColumn) ? $titleColumn : null;
        }

        $resourceTitleColumn = $this->relatedResourceTitleColumn($relation);

        if (!is_null($resourceTitleColumn)) {
            return $resourceTitleColumn;
        }

        foreach (['name', 'title'] as $titleColumn) {
            if ($this->belongsToSortColumnExists($relation, $titleColumn)) {
                return $titleColumn;
            }
        }

        return null;
    }

    private function relatedResourceTitleColumn(EloquentBelongsTo $relation): ?string
    {
        $relatedModel = get_class($relation->getRelated());

        foreach (Front::getRegisteredResources() as $resourceClass) {
            $resource = Front::makeResource($resourceClass);

            if ($resource->getModel() !== $relatedModel) {
                continue;
            }

            $titleColumn = $resource->search_title ?? $resource->title;

            return $this->belongsToSortColumnExists($relation, $titleColumn) ? $titleColumn : null;
        }

        return null;
    }

    private function belongsToSortColumnExists(EloquentBelongsTo $relation, ?string $column): bool
    {
        return is_string($column)
            && !Str::contains($column, ['.', '[', ']'])
            && $relation->getRelated()->getConnection()->getSchemaBuilder()->hasColumn($relation->getRelated()->getTable(), $column);
    }

    public function sortableIndexFields()
    {
        return $this->configurableColumnFields()->filter(function ($field) {
            if ($field->sortable === false) {
                return false;
            }

            if ($field->sortable === true || is_callable($field->sort_callback)) {
                return true;
            }

            $column = $field->sort_column ?? $field->column;

            return is_string($column)
                && !Str::contains($column, ['.', '[', ']']);
        })->mapWithKeys(function ($field, $index) {
            return [$this->indexColumnKey($field, $index) => $field];
        });
    }

    public function indexColumnKey($field, $index = null)
    {
        if (is_string($field->column)) {
            return $field->column;
        }

        return 'column_'.md5(get_class($field).':'.$field->title.':'.$index);
    }

    public function exportableIndexFields(array $columns)
    {
        $fields = $this->configurableIndexFieldsForColumns($columns)->filter(function ($field) {
            return $field->exportable !== false;
        });

        return $this->withExcelIdField($fields);
    }

    public function exportColumnKeys(array $columns)
    {
        return collect($columns)
            ->merge($this->requiredExportColumnKeys())
            ->unique()
            ->values()
            ->all();
    }

    public function requiredExportColumnKeys()
    {
        $fields = $this->configurableColumnFields()->map(function ($field, $index) {
            $field->front_column_key = $this->indexColumnKey($field, $index);

            return $field;
        });

        return $this->importableIndexFields($fields->pluck('front_column_key')->all())
            ->filter(function ($field) {
                return $this->fieldHasRequiredRule($field);
            })
            ->pluck('front_column_key')
            ->values()
            ->all();
    }

    public function importableIndexFields(array $columns = [])
    {
        $fields = count($columns) > 0
            ? $this->configurableIndexFieldsForColumns($columns)
            : $this->configurableIndexFields();
        $model = $this->getModel();
        $table = (new $model)->getTable();

        return $fields->filter(function ($field) use ($table) {
            if ($field->importable === false) {
                return false;
            }

            if ($field->importable === true || is_callable($field->import_callback)) {
                return true;
            }

            return is_string($field->column)
                && !Str::contains($field->column, ['.', '[', ']'])
                && $field->column !== $this->excelIdKeyName()
                && Schema::hasColumn($table, $field->column);
        });
    }

    public function excelIdKeyName(): string
    {
        $model = $this->getModel();
        $model = new $model;

        return $model->getKeyName();
    }

    public function excelIdHeading(): string
    {
        return 'ID';
    }

    public function excelIdHeadingKey(): string
    {
        return str($this->excelIdHeading())->slug('_')->toString();
    }

    public function excelIdField()
    {
        $field = IDInput::make(null, $this->excelIdKeyName())
            ->setResource($this)
            ->setSource('index');
        $field->title = $this->excelIdHeading();
        $field->front_column_key = $this->excelIdKeyName();

        return $field;
    }

    private function fieldHasRequiredRule($field)
    {
        return collect($field->getRules('store'))->contains(function ($rule) {
            if (!is_string($rule)) {
                return false;
            }

            return in_array('required', explode('|', $rule), true);
        });
    }

    public function excelHeadingForField($field): string
    {
        return $this->normalizeExcelHeading($field->title);
    }

    public function excelHeadingsForField($field): array
    {
        return collect([
            $field->title,
            $field->front_column_key,
            $field->column,
        ])->filter(function ($heading) {
            return is_string($heading);
        })->map(function ($heading) {
            return $this->normalizeExcelHeading($heading);
        })->unique()->values()->all();
    }

    public function normalizeExcelHeading($heading): string
    {
        return str($heading)->slug('_')->toString();
    }

    public function excelObjectForKey($key)
    {
        if (is_null($key) || $key === '') {
            return null;
        }

        return $this->globalIndexQuery()->find($key);
    }

    public function processExcel(array $data, string $direction = 'import', $row = null, $object = null): array
    {
        return $data;
    }

    public function importData($data, $object = null, $row = null)
    {
        return $data;
    }

    private function withExcelIdField($fields)
    {
        $key = $this->excelIdKeyName();

        return collect([$this->excelIdField()])
            ->merge($fields->reject(function ($field) use ($key) {
                return $field->column === $key;
            }))
            ->values();
    }

    public function configurableIndexFieldsForColumns(array $columns)
    {
        $fields = $this->configurableColumnFields()->map(function ($field, $index) {
            $field->front_column_key = $this->indexColumnKey($field, $index);

            return $field;
        });

        return collect($columns)->map(function ($column) use ($fields) {
            return $fields->firstWhere('front_column_key', $column);
        })->filter()->values();
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
        $filters = collect($this->cachedFilters())->mapWithKeys(function ($filter) use ($try, &$exist_filter_value) {
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
        $url .= '?'.http_build_query($filters->toArray());

        return $url;
    }

    public function activeFiltersCount(?array $values = null): int
    {
        $values ??= request()->all();
        $count = 0;

        foreach ($this->getFilters() as $filter) {
            foreach ($this->filterInputs($filter) as $input) {
                if (!$input->show_on_filter) {
                    continue;
                }

                $value = $values[$filter->slug] ?? request()->input($filter->slug);

                if (filled($value)) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    public function filterInputs($filter)
    {
        $field = $filter->field();

        if ($field instanceof Collection) {
            return $field->filter();
        }

        if (is_array($field)) {
            return collect($field)->filter();
        }

        return collect([$field])->filter();
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
                $base_url = str_replace('{'.$result.'}', $value, $base_url);
            } else {
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
