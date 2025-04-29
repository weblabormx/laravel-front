<?php

namespace WeblaborMx\Front\Filters;

use Illuminate\Support\Str;

class Filter
{
    public $resource;
    public $slug;
    public $default = '';
    public $show = true;
    public $visible = true;
    public $execute_before = true;

    public function __construct()
    {
        if (!isset($this->slug)) {
            $this->slug = Str::slug(Str::snake(class_basename(get_class($this))), '_');
        }
    }

    /*
     * Needed functions
     */

    public function default()
    {
        return $this->default;
    }

    public function apply($query, $value)
    {
        return $query;
    }

    public function field()
    {
        return;
    }

    /*
     * Hidden functions
     */

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function formHtml()
    {
        $input = $this->field();
        if (is_null($input)) {
            return;
        }
        return $input->setColumn($this->slug)->formHtml();
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function show($result)
    {
        if (!is_string($result) && is_callable($result)) {
            $result = $result();
        }
        $this->show = $result;
        return $this;
    }
}
