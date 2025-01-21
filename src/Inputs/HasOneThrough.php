<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Traits\InputRelationship;

class HasOneThrough extends Input
{
    use InputRelationship;

    public $relation;
    public $model_name;
    public $relation_front;
    public $search_field;
    public $empty_title;
    public $relation_function;
    public $default_data = [];
    public $show_placeholder = true;

    public function __construct($title, $column = null, $extra = null, $source = null)
    {
        $this->relation = Str::snake($column);
        $this->column = 'has_one_trough:' . $title;
        $this->extra = $extra;
        $this->source = $source;

        if (!isset($this->extra)) {
            $front = Str::singular($this->relation);
            $front = ucfirst(Str::camel($front));
            $this->extra = $title;
        }

        $this->model_name = $this->extra;
        $this->relation_front = Front::makeResource($this->model_name, $this->source);
        $class = $this->relation_front->getModel();
        $this->title = $this->relation_front->label;

        $this->load();
    }

    public function setResource($resource)
    {
        $relation = $this->relation;
        if (!method_exists($resource, 'getModel')) {
            return parent::setResource($resource);
        }

        $class = $resource->getModel();
        $model = new $class();
        if (method_exists($model, $relation)) {
            $relation_function = $model->$relation();
            $this->relation_function = $relation_function;
        } else {
            abort(506, 'The relation ' . $relation . ' does not exists in ' . $class);
        }
        return parent::setResource($resource);
    }

    public function getValue($object)
    {
        $relation = $this->relation;
        if (!is_object($object->$relation)) {
            return '--';
        }

        $title_field = $this->search_field ?? $this->relation_front->search_title;
        $value = $object->$relation->$title_field;
        if (!isset($this->link)) {
            $this->link = $this->relation_front->getBaseUrl() . '/' . $object->$relation->getKey();
        }
        if (!isset($value)) {
            return '--';
        }
        return $value;
    }

    public function form()
    {
        $relation_front = $this->relation_front;

        $model = $this->relation_front->getModel();
        $model = new $model();

        if (isset($this->force_query)) {
            $force_query = $this->force_query;
            $query = $force_query($model);
        } else {
            $query = $this->relation_front->globalIndexQuery();
        }

        if (isset($this->filter_query)) {
            $filter_query = $this->filter_query;
            $query = $filter_query($query);
        }

        $options = $query->get();
        if (isset($this->filter_collection)) {
            $filter_collection = $this->filter_collection;
            $options = $filter_collection($options);
        }

        // Get default value
        $relation = $this->relation;
        $value = $this->resource?->object?->$relation?->getKey();
        if (isset($value)) {
            $this->default_value = $value;
            $this->default_value_force = true;
        }

        $title = $this->search_field ?? $this->relation_front->search_title;
        $options = $options->pluck($title, $model->getKeyName());
        $select = Select::make($this->title, $this->column)
            ->options($options)
            ->default($this->default_value, $this->default_value_force)
            ->size($this->size)
            ->setEmptyTitle($this->empty_title)
            ->withMeta($this->attributes)
            ->setPlaceholder($this->show_placeholder);
        return $select->form();
    }

    public function hidePlaceholder()
    {
        $this->show_placeholder = false;
        return $this;
    }

    public function defaultData($data)
    {
        $this->default_data = $data;
        return $this;
    }

    public function processData($data)
    {
        if (!isset($data[$this->column]) || is_null($data[$this->column])) {
            unset($data[$this->column]);
            return $data;
        }

        $value = $data[$this->column];
        $relation = $this->relation;
        $column = $this->relation_function->getForeignKeyName();

        // Remove items on the relation
        $this->relation_function->getParent()->query()->where($this->relation_function->getFirstKeyName(), $this->resource->object->$column)->delete();

        // Create new item. Consider it works only with 1 item related.
        $this->relation_function->getParent()->create(array_merge($this->default_data, [
            $this->relation_function->getFirstKeyName() => $this->resource->object->$column,
            $this->relation_function->getSecondLocalKeyName() => $value
        ]));

        unset($data[$this->column]);
        return $data;
    }
}
