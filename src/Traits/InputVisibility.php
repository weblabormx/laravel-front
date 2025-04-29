<?php

namespace WeblaborMx\Front\Traits;

trait InputVisibility
{
    public $show = true;
    public $show_before = true;
    public $show_on_index = true;
    public $show_on_show = true;
    public $show_on_edit = true;
    public $show_on_create = true;

    public function hideFromIndex()
    {
        $this->show_on_index = false;
        return $this;
    }

    public function hideFromDetail()
    {
        $this->show_on_show = false;
        return $this;
    }

    public function hideWhenCreating()
    {
        $this->show_on_create = false;
        return $this;
    }

    public function hideWhenUpdating()
    {
        $this->show_on_edit = false;
        return $this;
    }

    public function onlyOnIndex()
    {
        $this->show_on_index = true;
        $this->show_on_show = false;
        $this->show_on_edit = false;
        $this->show_on_create = false;
        return $this;
    }

    public function onlyOnDetail()
    {
        $this->show_on_index = false;
        $this->show_on_show = true;
        $this->show_on_edit = false;
        $this->show_on_create = false;
        return $this;
    }

    public function onlyOnCreate()
    {
        $this->show_on_index = false;
        $this->show_on_show = false;
        $this->show_on_edit = false;
        $this->show_on_create = true;
        return $this;
    }

    public function onlyOnEdit()
    {
        $this->show_on_index = false;
        $this->show_on_show = false;
        $this->show_on_edit = true;
        $this->show_on_create = false;
        return $this;
    }

    public function onlyOnForms()
    {
        $this->show_on_index = false;
        $this->show_on_show = false;
        $this->show_on_edit = true;
        $this->show_on_create = true;
        return $this;
    }

    public function exceptOnForms()
    {
        $this->show_on_index = true;
        $this->show_on_show = true;
        $this->show_on_edit = false;
        $this->show_on_create = false;
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

    public function shouldBeShown()
    {
        return $this->show && $this->show_before;
    }

    public function showOnHere()
    {
        $var = $this->source ?? 'index';
        $var = $var == 'update' ? 'edit' : $var;
        $var = $var == 'store' ? 'create' : $var;
        $var = 'show_on_' . $var;
        return $this->$var && $this->shouldBeShown();
    }
}
