<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Str;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Traits\InputRelationship;

class BelongsToMany extends Input
{
    use InputRelationship;

	public $relation, $model_name, $relation_front, $search_field, $empty_title;
	public $show_placeholder = true, $is_multiple = true;

    public function __construct($title, $relation = null, $model_name = null, $source = null)
    {
        $this->model_name = $model_name;
        $this->relation = Str::snake(Str::plural($relation));
        $this->source = $source;

        if (!isset($this->model_name)) {
            $front = Str::singular($this->relation);
            $front = ucfirst(Str::camel($front));
            $this->model_name = $title;
        }

        $this->relation_front = Front::makeResource($this->model_name, $this->source);
        if (!$this->relation_front->canIndex()) {
            $this->show = false;
        }
        $class = $this->relation_front->getModel();
        $this->title = $this->relation_front->label;
        $this->load();
    }

    public function setResource($resource)
    {
        $relation = $this->relation;
        $this->column = $relation . '_mtm';
        if (is_object($resource->object)) {
            $this->default_value = $resource->object->{$this->relation}->pluck('id');
            $this->default_value_force = true;
        }

        return parent::setResource($resource);
    }

    public function getValue($object)
    {
        $relation = $this->relation;
        if (!is_object($object->$relation)) {
            return '--';
        }

        $title_field = $this->search_field ?? $this->relation_front->title;
        $value = $object->$relation->pluck($title_field);

        if (strlen($value) <= 0) {
            return '--';
        }

        $value = $value->map(function ($item) {
            return "<li>" . $item . "</li>";
        });
        return '<ul>' . $value->implode('') . '</ul>';
    }

	public function form()
	{
		$model = $this->relation_front->getModel();
		$model = new $model;

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

		$title = $this->search_field ?? $this->relation_front->search_title;
		$options = $options->pluck($title, $model->getKeyName());
		$select = Select::make($this->title, $this->column)
			->options($options)
			->default($this->default_value, $this->default_value_force)
			->size($this->size)
			->setEmptyTitle($this->empty_title)
			->withMeta($this->attributes)
			->setPlaceholder($this->show_placeholder);
		if($this->is_multiple) {
			$select = $select->multiple();
		}
		return $select->form();
	}

	public function processDataAfterValidation($data)
	{
		unset($data[$this->column]);
		return $data;
	}

    public function processAfterSave($object, $request)
    {
        $values = $request->{$this->column};
        $object->{$this->relation}()->sync($values);
    }

    public function setSearchField($field)
    {
        $this->search_field = $field;
        return $this;
    }

    public function setEmptyTitle($value)
    {
        $this->empty_title = $value;
        return $this;
    }

	public function hidePlaceholder()
	{
		$this->show_placeholder = false;
		return $this;
	}

	public function noMultiple()
	{
		$this->is_multiple = false;
		return $this;
	}
}
