<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Facades\Gate;

trait HasPermissions
{
    public function canIndex()
    {
        return Gate::allows('viewAny', $this->getModel()) && in_array('index', $this->actions);
    }

    public function canCreate()
    {
        return Gate::allows('create', $this->getModel()) && in_array('create', $this->actions);
    }

    public function canShow($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return Gate::allows('view', $object) && in_array('show', $this->actions);
    }

    public function canUpdate($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return Gate::allows('update', $object) && in_array('edit', $this->actions);
    }

    public function canRemove($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return Gate::allows('delete', $object) && in_array('destroy', $this->actions);
    }

}
