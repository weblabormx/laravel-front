<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Traits\InputWithActions;
use WeblaborMx\Front\Traits\InputWithLinks;

class Panel extends Component
{
    use InputWithActions;
    use InputWithLinks;

    public $is_panel = true;
    public $description;

    public function load()
    {
        $this->show_before = count($this->fields()) > 0;
    }

    public function formHtml()
    {
        $panel = $this;
        return view('front::components.panel-form', compact('panel'));
    }

    public function showHtml($object)
    {
        $panel = $this;
        $field = $this->fields()->first();
        $is_input = is_object($field) ? $field->is_input : false;
        return view('front::components.panel', compact('panel', 'object', 'is_input'));
    }

    public function html()
    {
        $input = $this;
        $value = $this->showHtml(null);
        return view('front::input-outer', compact('value', 'input'))->render();
    }

    public function getValue($object)
    {
        return $this->fields()->map(function ($item) use ($object) {
            return $item->showHtml($object);
        })->implode('');
    }

    public function form()
    {
        return $this->fields()->map(function ($item) {
            return $item->formHtml();
        })->implode('');
    }

    private function filterFields($where, $model = null)
    {
        $where = $where == 'update' ? 'edit' : $where;
        $where = $where == 'store' ? 'create' : $where;
        return collect($this->column)->filter(function ($item) {
            return isset($item);
        })->flatten()->map(function ($item) use ($model) {
            return $item->setDefaultValueFromAttributes($model);
        })->filter(function ($item) use ($where) {
            if (is_null($where)) {
                return true;
            }
            $field = 'show_on_' . $where;
            if (!isset($item->$field)) {
                return true;
            }
            return $item->$field && $item->shouldBeShown();
        });
    }

    public function fields($model = null)
    {
        return $this->filterFields($this->source, $model);
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function processData($inputs)
    {
        // Get fields processing
        $fields = $this->filterFields(null);

        $fields->each(function ($item) use (&$inputs) {
            $inputs = $item->processData($inputs);
        });
        return $inputs;
    }
}
