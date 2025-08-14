<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Facades\Gate;

trait HasPermissions
{
    public function canIndex()
    {
        return in_array('index', $this->actions) && Gate::allows('viewAny', $this->getModel());
    }

    public function canIndexDeleted()
    {
        if(!$this->canRemove() || !Gate::allows('viewDeleted', $this->getModel()) || !in_array('index', $this->actions)) {
            return false;
        }
        $model = $this->getModel();
        $model = new $model;
        return method_exists($model, 'trashed');
    }

    public function canCreate()
    {
        return in_array('create', $this->actions) && Gate::allows('create', $this->getModel());
    }

    public function canShow($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return in_array('show', $this->actions) && Gate::allows('view', $object);
    }

    public function canUpdate($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return in_array('edit', $this->actions) && Gate::allows('update', $object);
    }

    public function canRemove($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return in_array('destroy', $this->actions) && Gate::allows('delete', $object);
    }

    public function canRestore($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return in_array('index', $this->actions) && Gate::allows('restore', $object);
    }

    public function canForceDelete($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return in_array('destroy', $this->actions) && Gate::allows('forceDelete', $object);
    }
}
