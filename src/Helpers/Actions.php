<?php

namespace WeblaborMx\Front\Helpers;

use Illuminate\Support\Str;

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
        if (!$this->isEloquent()) {
            return false;
        }
        return $this->front->canShow($this->object);
    }

    public function canUpdate()
    {
        if (!$this->isEloquent()) {
            return false;
        }
        return $this->front->canUpdate($this->object);
    }

    public function canRemove()
    {
        if (!$this->isEloquent()) {
            return false;
        }
        return $this->front->canRemove($this->object);
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

    public function upUrl()
    {
        $link = $this->base_url.'/'.$this->object->getKey().'/sortable/up';
        return $link;
    }

    public function downUrl()
    {
        $link = $this->base_url.'/'.$this->object->getKey().'/sortable/down';
        return $link;
    }

    private function isEloquent()
    {
        return is_subclass_of($this->object, 'Illuminate\Database\Eloquent\Model');
    }

    public function isSortable()
    {
        return isset(class_uses($this->object)['Spatie\EloquentSortable\SortableTrait']);
    }

    public function getActions($object)
    {
        return $this->front->getActions()->where('show_on_index', 1)->filter(function ($item) use ($object) {
            return $item->hasPermissions($object);
        })->map(function ($item) use ($object) {
            $item->url = $this->front->getBaseUrl()."/{$object->getKey()}/action/{$item->slug}";
            return $item;
        });
    }
}
