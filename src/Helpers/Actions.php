<?php

namespace WeblaborMx\Front\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class Actions
{
    private $front;
    private $object;
    private $base_url;
    private $edit_link;
    private $show_link;

	public function __construct($front, $object, $base_url, $edit_link = '{key}/edit', $show_link = '')
    {
        $this->front     = $front;
        $this->object    = $object;
        $this->edit_link = $edit_link ?? '{key}/edit';
        $this->show_link = $show_link;
        $this->base_url  = $base_url;
        return $this;
    
    }

    public function canShow()
    {
        if(!$this->isEloquent()) {
            return false;
        }
        return Gate::allows('view', $this->object) && in_array('show', $this->front->actions);
    }

    public function canUpdate()
    {
        if(!$this->isEloquent()) {
            return false;
        }
        return Gate::allows('update', $this->object) && in_array('edit', $this->front->actions);
    }

    public function canRemove()
    {
        if(!$this->isEloquent()) {
            return false;
        }
        return Gate::allows('delete', $this->object) && in_array('destroy', $this->front->actions);
    }

    public function showUrl()
    {
        $link = $this->base_url.'/'.$this->object->getKey();
        return $link.$this->show_link;
    }

    public function updateUrl()
    {
        $edit = str_replace('{key}', $this->object->getKey(), $this->edit_link);
        $link = $this->base_url.'/'.$edit;
        return $link.$this->show_link;
    }

    public function removeUrl()
    {
        $link = $this->base_url.'/'.$this->object->getKey();
        return $link;
    }

    private function isEloquent()
    {
        return is_subclass_of($this->object, 'Illuminate\Database\Eloquent\Model');
    }
}