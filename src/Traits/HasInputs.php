<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use WeblaborMx\Front\Components\Panel;
use Illuminate\Support\Str;
use WeblaborMx\Front\Components\HtmlableComponent;
use WeblaborMx\Front\Inputs\Input;
trait HasInputs
{
    private $relations = ['HasMany', 'MorphMany'];
    public $fields_function = 'fields';

    /*
     * Functions
     */

    public function fields()
    {
        return [];
    }

    public function setFields($fields, $function = 'fields')
    {
        $this->functions_values[$function] = $fields;
        return $this;
    }

    public function getFields()
    {
        $fields_function = $this->fields_function;
        if (isset($this->functions_values[$fields_function])) {
            return $this->functions_values[$fields_function];
        }
        // Add priority to this
        if ($fields_function != 'fields') {
            return $this->$fields_function();
        }
        // Dynamic fields name
        if (method_exists($this, 'getCurrentViewName') && $this->getFieldsOnView() != false) {
            return $this->getFieldsOnView();
        }

        return $this->$fields_function();
    }

    private function getFieldsOnView()
    {
        $view = $this->getCurrentViewName();
        if ($view != 'normal') {
            $function = 'fieldsOn' . ucfirst(Str::camel($view)) . 'View';
            $exists = method_exists($this, $function);
            if ($exists) {
                return $this->$function();
            }
        }
        return false;
    }

    // This need to be improved
    // ^ Yep, the comment above is right
    private function filterFields($where, $flatten = false)
    {
        $sum = [];
        if ($where == 'index' || $flatten) {
            $sum = collect($this->getFields())->flatten()->filter(function ($item) {
                return is_object($item) && class_basename(get_class($item)) == 'Panel';
            })->filter(function ($item) use ($where) {
                $field = 'show_on_' . $where;
                return $item->$field && $item->shouldBeShown();
            })->map(function ($panel) {
                return collect($panel->column)->flatten()->map(function ($field) use ($panel) {
                    if (is_array($field)) {
                        return $field;
                    }
                    $field->show_on_index = !$panel->show_on_index ? false : $field->show_on_index;
                    $field->show_on_show = !$panel->show_on_show ? false : $field->show_on_show;
                    $field->show_on_edit = !$panel->show_on_edit ? false : $field->show_on_edit;
                    $field->show_on_create = !$panel->show_on_create ? false : $field->show_on_create;
                    return $field;
                })->all();
            })->flatten()->filter(function ($item) use ($where) {
                $field = 'show_on_' . $where;
                return $item->$field && $item->shouldBeShown();
            })->filter(function ($item) {
                if (!isset($this->hide_columns) || is_null($item->column)) {
                    return true;
                }
                $columns = $this->hide_columns;
                if (!is_array($columns)) {
                    $columns = [$columns];
                }
                if (!is_string($item->column) && is_callable($item->column)) {
                    return true;
                }
                return !collect($columns)->contains($item->column);
            })->map(function ($item) use ($where) {
                return $item->setResource($this)->setSource($where);
            });
        }

        $return = $this->processInputFieldsCollection(
            $this->getFields(),
            $where
        );

        $return = $return->merge($sum)->map(function ($item) {
            if (isset($item->get_value_from)) {
                $column = $item->get_value_from;
                $item->setValue($this->object?->$column);
            }
            return $item;
        });

        return $return;
    }

    public function allFields()
    {
        return $this->filterFields('show');
    }

    public function indexFields()
    {
        return $this->filterFields('index')->filter(function ($item) {
            return $item->is_input;
        });
    }

    /** @return Collection */
    private function processInputFieldsCollection($fields = [], $where = 'index')
    {
        $collection = collect($fields)
            ->flatten()
            ->map(function ($item) {
                if ($item instanceof Input) {
                    return $item;
                } elseif ($item instanceof Htmlable) {
                    return new HtmlableComponent($item);
                }

                return null;
            })->filter() // First: cast any Htmlable and remove invalid objects
            ->filter(function ($item) use ($where) {
                $field = 'show_on_' . $where;
                return $item->$field && $item->shouldBeShown();
            }) // Second: Check if the field should be shown by the config
            ->map(function ($item) use ($where) {
                return $item->setResource($this)->setSource($where);
            }) // Third: Sync the state of the fields to the current one
            ->map(function ($item) use ($where) {
                // This should probably be responsability of the Panel. Todo: add method to deconstruct
                if ($item instanceof Panel) {
                    $item->column = $this->processInputFieldsCollection($item->column, $where);

                    return $item;
                }

                return $item;
            }) // Fourth: Unpack items from Panels
            ->filter(function ($item) {
                if (!isset($this->hide_columns) || is_null($item->column)) {
                    return true;
                }

                $columns = $this->hide_columns;

                if (!is_array($columns)) {
                    $columns = [$columns];
                }

                return !in_array($item->column, $columns);
            }); // Fifth: Check if the column should be hidden

        return $collection;
    }

    private function addAtLeastOnePanel($type)
    {
        // Get all fields that are not relationships
        $fields = $this->filterFields($type)->filter(function ($item) {
            return !Str::contains(class_basename(get_class($item)), $this->relations);
        });

        // Get components or elements that dont need to have a panel
        $components = $fields->filter(function ($item) {
            return class_basename(get_class($item)) == 'Panel' || !$item->needs_to_be_on_panel;
        })->filter(function ($item) {
            return class_basename(get_class($item)) != 'Panel' || $item->fields()->count() > 0;
        });

        // Get other fields that were not gotten on $components
        $new_panels = [];
        $last_key = -99;
        $field_key = null;
        $fields = $fields->filter(function ($item, $key) {
            return class_basename(get_class($item)) != 'Panel' && $item->needs_to_be_on_panel;
        })->each(function ($item, $key) use (&$new_panels, &$last_key, &$field_key) {
            if ($key - 1 != $last_key) {
                $field_key = $key;
            }
            $new_panels[$field_key][] = $item;
            $last_key = $key;
        });

        // Create panels for this new_panels
        $new_panels = collect($new_panels)->map(function ($item) {
            return Panel::make('', $item);
        });
        return $components->union($new_panels)->sortKeys()->values();
    }

    public function showPanels()
    {
        return $this->addAtLeastOnePanel('show');
    }

    public function showRelations()
    {
        return $this->filterFields('show')->filter(function ($item) {
            return Str::contains(class_basename(get_class($item)), $this->relations);
        })->values();
    }

    public function editPanels()
    {
        return $this->addAtLeastOnePanel('edit');
    }

    public function createPanels()
    {
        return $this->addAtLeastOnePanel('create');
    }

    public function changeFieldsFunction($fields_function)
    {
        $this->fields_function = $fields_function;
        return $this;
    }

    public function processData($inputs)
    {
        // Remove from the inputs fields marked as null
        if (isset($this->ignore_if_null)) {
            foreach ($this->ignore_if_null as $input) {
                if (is_null($inputs[$input])) {
                    unset($inputs[$input]);
                }
            }
        }

        // Remove redirect url helper
        unset($inputs['redirect_url']);

        // Get fields processing
        $fields = $this->filterFields($this->source == 'update' ? 'edit' : 'create', true);

        // Remove autocomplete helper input
        $autocomplete_fields = $fields->filter(function ($item) {
            return isset($item->searchable) && $item->searchable;
        })->map(function ($item) {
            return $item->column . 'ce';
        })->values()->each(function ($item) use (&$inputs) {
            unset($inputs[$item]);
        });

        $fields->filter(function ($item) use ($inputs) {
            return $item->is_input;
        })->each(function ($item) use (&$inputs) {
            $inputs = $item->processData($inputs);
            if (isResponse($inputs)) {
                return false;
            }
        });
        return $inputs;
    }

    public function processDataAfterValidation($inputs)
    {
        // Get fields processing
        $fields = $this->filterFields($this->source == 'update' ? 'edit' : 'create', true)->filter(function ($item) {
            return $item->is_input;
        });

        // Remove inputs of conditionals not true
        $fields->filter(function ($item) {
            return !$item->validateConditional(request()->all());
        })->each(function ($item) use (&$inputs) {
            unset($inputs[$item->column]);
        });

        // Show only validated inputs
        $fields = $fields->filter(function ($item) {
            return $item->validateConditional(request()->all());
        });

        // Process data on inputs
        $fields->each(function ($item) use (&$inputs) {
            $inputs = $item->processDataAfterValidation($inputs);
        });

        // Rename inputs if needed
        $fields->filter(function ($item) {
            return isset($item->rename_after);
        })->each(function ($item) use (&$inputs) {
            $inputs[$item->rename_after] = $inputs[$item->column];
            unset($inputs[$item->column]);
        });

        return $inputs;
    }

    public function processAfterSave($object, $request)
    {
        // Get fields processing
        $fields = $this->filterFields($this->source == 'update' ? 'edit' : 'create', true);
        $fields->filter(function ($item) {
            return $item->is_input;
        })->each(function ($item) use ($object, $request) {
            $item->processAfterSave($object, $request);
        });
    }
}
