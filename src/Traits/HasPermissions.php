<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Facades\Gate;

trait HasPermissions
{
    public function canIndex()
    {
        return cache()->store('array')->rememberForever('HasPermission:canIndex:'.$this->getModel(), function() {
            return in_array('index', $this->actions) && Gate::allows('viewAny', $this->getModel());
        });
    }

    public function canIndexDeleted()
    {
        return cache()->store('array')->rememberForever('HasPermission:canIndexDeleted:'.$this->getModel(), function() {
            if(!in_array('destroy', $this->actions) || !Gate::allows('viewDeleted', $this->getModel()) || !in_array('index', $this->actions)) {
                return false;
            }
            $model = $this->getModel();
            $model = new $model;
            return method_exists($model, 'trashed');
        });
    }

    public function canCreate()
    {
        return cache()->store('array')->rememberForever('HasPermission:canCreate:'.$this->getModel(), function() {
            return in_array('create', $this->actions) && Gate::allows('create', $this->getModel());
        });
        
    }

    public function canShow($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return cache()->store('array')->rememberForever('HasPermission:canShow:'.$this->getModel().':'.$object->getKey(), function() use ($object) {
            return in_array('show', $this->actions) && Gate::allows('view', $object);
        });
        
    }

    public function canUpdate($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return cache()->store('array')->rememberForever('HasPermission:canUpdate:'.$this->getModel().':'.$object->getKey(), function() use ($object) {
            return in_array('edit', $this->actions) && Gate::allows('update', $object);
        });
    }

    public function canRemove($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return cache()->store('array')->rememberForever('HasPermission:canRemove:'.$this->getModel().':'.$object->getKey(), function() use ($object) {
            return in_array('destroy', $this->actions) && Gate::allows('delete', $object);
        });
    }

    public function canRestore($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return cache()->store('array')->rememberForever('HasPermission:canRestore:'.$this->getModel().':'.$object->getKey(), function() use ($object) {
            return in_array('index', $this->actions) && Gate::allows('restore', $object);
        });
    }

    public function canForceDelete($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return cache()->store('array')->rememberForever('HasPermission:canForceDelete:'.$this->getModel().':'.$object->getKey(), function() use ($object) {
            return in_array('destroy', $this->actions) && Gate::allows('forceDelete', $object);
        });
    }
}
