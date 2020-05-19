<?php

namespace WeblaborMx\Front\Traits;

trait IsValidated
{
    public function makeValidation($fields, $data)
    {
        // Get rules on inputs of the resource
        $rules = $fields->filter(function($item) {
            return count($item->getRules($this->source))>0;
        })->map(function($item) {
            return $item->setResource($this);
        })->mapWithKeys(function($item) {
            return [$this->validatorGetColumn($item->column) => $item->getRules($this->source)];
        })->all();

        // Update rules on inputs
        foreach ($fields as $field) {
            $rules = $field->editRules($rules);
        }
        
        // Execute validator function on fields
        $fields->map(function($item) {
            return $item->setResource($this);
        })->each(function($item) use ($data) {
            return $item->validate($data);
        });

        // Validate all the rules
        $attributes = $fields->filter(function($item) {
            return isset($item->column) && isset($item->title) && is_string($item->column) && is_string($item->title);
        })->mapWithKeys(function($item) {
            return [$this->validatorGetColumn($item->column) => $item->title];
        })->toArray();
        
        \Validator::make($data, $rules, [], $attributes)->validate();
    }

    private function validatorGetColumn($column)
    {
        $column = str_replace('[', '.', $column);
        $column = str_replace(']', '', $column);
        $column = trim($column, '.');
        return __($column);
    }
}
