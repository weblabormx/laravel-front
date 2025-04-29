<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Inputs\Text;
use WeblaborMx\Front\Inputs\Hidden;

trait HasMassiveEditions
{
    public $massive_class;
    public $show_massive = false;

    /*
     * Functions
     */

    public function setMassiveClass($class)
    {
        if (!is_null($class)) {
            $class = new $class();
        }
        $this->massive_class = $class;
        return $this;
    }

    public function enableMassive($value = true)
    {
        $this->show_massive = $value;
        return $this;
    }

    public function getFront()
    {
        return $this;
    }

    /*
     * Helpers
     */

    public function getMassiveForms()
    {
        $forms = [];
        if (!isset($this->massive_class) || (isset($this->massive_class) && $this->massive_class->new_rows_available)) {
            $forms[] = Text::make(__('New rows'), 'rows');
        }
        foreach (request()->except('rows') as $key => $value) {
            $forms[] = Hidden::make($key, $key)->setValue($value);
        }
        return $forms;
    }

    public function getTableHeadings($object = null)
    {
        // Always show ID column
        $headings = ['ID'];

        // Get front
        $front = $this->getFront();
        if (!is_null($object)) {
            $front = $front->setObject($this->getResults($object)->first());
        }

        // Show fields that are on index and that can be edited
        $fields = $front->indexFields()->filter(function ($item) {
            return $item->show_on_edit;
        });

        // Save titles to the result
        foreach ($fields as $field) {
            $headings[] = $field->title;
        }
        $this->headings = $headings;

        // Return the headings
        return $headings;
    }

    public function getTableValues($object)
    {
        // Get front
        $front = $this->getFront()->setObject($object);

        // Get id value
        $id = is_a($object, 'Illuminate\Database\Eloquent\Model') ? $object->getKey() : $object->id;

        // Start the result with the id result
        $values = [$id];

        // Show fields that are on index and that can be edited
        $fields = $front->indexFields()->filter(function ($item) {
            return $item->show_on_edit;
        });

        // Save values to the result
        foreach ($fields as $field) {
            $column = $field->column;
            $field = $field->setColumn($id.'['.$field->column.']')->default($object->$column, true);
            if (get_class($field) == 'WeblaborMx\Front\Inputs\Number') {
                $field = $field->size(80);
            }
            $values[] = $field->form();
        }

        // Return result
        return $values;
    }

    public function getExtraTableValues()
    {
        $result = [];
        $front = $this->getFront();

        // Show fields that are on index and that can be created
        $fields = $front->indexFields()->filter(function ($item) {
            return $item->show_on_create;
        });

        if (!isset($this->massive_class) || (isset($this->massive_class) && $this->massive_class->new_rows_available)) {
            for ($i = 0; $i < (request()->rows ?? 0); $i++) {
                $values = collect($this->headings)->flip()->map(function ($item) {
                    return '';
                });
                foreach ($fields as $field) {
                    $column = $field->column;
                    $field = $field->setColumn('new'.$i.'['.$field->column.']');
                    if (get_class($field) == 'WeblaborMx\Front\Inputs\Number') {
                        $field = $field->size(80);
                    }
                    $values[$field->title] = $field->form();
                }
                $result[] = $values;
            }
        }
        return $result;
    }

    public function getTableButtons()
    {
        $buttons = [];
        if (isset($this->massive_class)) {
            foreach ($this->massive_class->buttons as $function => $title) {
                $buttons[$function] = $title;
            }
        }
        $buttons[null] = '<i class="fa fa-save"></i> '.__('Save');
        return $buttons;
    }
}
