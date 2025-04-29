<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Str;

class MorphTo extends Input
{
    public $types;
    public $types_models;
    public $input_formatted = false;

    public function load()
    {
        $this->title = ucwords(str_replace('-', ' ', Str::kebab($this->title)));
    }

    public function types($types)
    {
        // Create the types as front objects
        $this->types = collect($types)->map(function ($item) {
            return new $item($this->source);
        });

        // Detect aliases
        $loader = AliasLoader::getInstance();
        $aliases = collect($loader->getAliases());

        // Make a new array with all models of the types
        $this->types_models = $this->types->mapWithKeys(function ($item) use ($aliases) {
            $alias = $item->getModel();

            // If model has alias use alias instead of model
            if ($aliases->contains($item->getModel())) {
                $alias = $aliases->search($item->getModel());
            }

            return [$item->label => $alias];
        });

        // Return object
        return $this;
    }

    private function getFrontOnClass($class)
    {
        return $this->types->filter(function ($item) use ($class) {
            return $item->getModel() == $class;
        })->first();
    }

    public function getValue($object)
    {
        $relation = $this->column;
        if (!is_object($object->$relation)) {
            return '--';
        }

        $value = $object->$relation;
        if (!isset($value)) {
            return '--';
        }
        $class = get_class($value);
        if (!isset($this->types_models)) {
            abort(405, 'Please defines the possible types for the polimorfic values of '.$this->column.' with types() function');
        }
        $result = $this->types_models->search($class);
        if (!isset($result)) {
            return '--';
        }
        $front = $this->getFrontOnClass($class);
        if (is_null($front)) {
            abort(405, $class.' front is not defined on the types');
        }
        $this->link = $front->getBaseUrl().'/'.$value->getKey();
        $title_field = $front->title;
        return $value->$title_field;
    }

    public function form()
    {
        // if is hidden
        if ($this->hide && ((request()->filled($this->column.'_type') && request()->filled($this->column.'_id')) || $this->source == 'edit')) {
            return collect([
                Hidden::make($this->title, $this->column.'_type'),
                Hidden::make($this->title, $this->column.'_id')
            ])->map(function ($item) {
                return (string) $item->formHtml();
            })->implode('');
        }

        // Get options for the type select
        $options = $this->types_models->flip();

        // Add type select to fields
        $fields = collect([
            Select::make($this->title.' '.__('Type'), $this->column.'_type')->options($options),
        ]);

        // Add every type field
        foreach ($this->types as $type) {
            // Generate new field name
            $column = $this->column.'_id_'.Str::slug($type->label, '_');

            // Get model
            $model = $options->search($type->label);
            $morph_field = $this->column;
            $type_field = $this->column.'_type';
            $id_field = $this->column.'_id';
            $title = $type->title;

            // Show autocomplete field
            $field = Autocomplete::make($type->label, $column)
                ->setUrl($type->getBaseUrl().'/search')->conditionalOld($type_field, $model);

            // if we have an object and a value, set it and its for this type
            if (isset($this->resource) && isset($this->resource->object) && $this->resource->object->$type_field == $model && isset($this->resource->object->$morph_field)) {
                $field = $field->setText($this->resource->object->$morph_field->$title)->setValue($this->resource->object->$id_field);
            }

            // if we have a prefilled value set
            elseif (request()->filled($type_field) && request()->filled($id_field)) {
                $type = request()->$type_field;
                if ($type == $model) {
                    $id = request()->$id_field;
                    $object = $type::find($id);
                    $field = $field->setText($object->$title)->setValue($id);
                }
            }

            // Add to fields array
            $fields[] = $field;
        }

        // Returns html
        return $fields->map(function ($item) {
            return $item->formHtml();
        })->implode('');
    }

    public function editRules($rules)
    {
        // Dont do anything if there isnt any rule to this element
        if (!isset($rules[$this->column])) {
            return $rules;
        }

        $rule = $rules[$this->column];
        unset($rules[$this->column]);
        $rules[$this->column.'_type'] = $rule;
        $rules[$this->column.'_id'] = $rule;
        return $rules;
    }

    public function processData($data)
    {
        $type_field = $this->column.'_type';
        $id_field = $this->column.'_id';

        // If type doesnt have any value
        if (!isset($data[$type_field])) {
            return $this->removeCreatedFields($data, $id_field);
        }

        // If id field is already defined
        if (isset($data[$id_field])) {
            return $this->removeCreatedFields($data, $id_field);
        }

        // Set the correct value
        $model = $data[$type_field];				// Value on xx_type column gotten
        $key = $this->types_models->search($model); // Find the label of the model
        $type = Str::slug($key, '_'); 					// Convert to lower case
        $new_type_field = $id_field.'_'.$type;		// Get the field name of the type saved
        $data[$id_field] = $data[$new_type_field];  // Set the id to the value on the new type field
        return $this->removeCreatedFields($data, $id_field);
    }

    private function removeCreatedFields($data, $id_field)
    {
        // Remove the created fields from the array
        $ids_columns = collect($data)->keys()->filter(function ($item) use ($id_field) {
            return Str::contains($item, $id_field.'_');
        });
        foreach ($ids_columns as $column) {
            unset($data[$column]);
        }
        return $data;
    }
}
