<?php

namespace WeblaborMx\Front\Traits;

trait HasActions
{
    public function actions()
    {
        return [];
    }

    public function index_actions()
    {
        return [];
    }

    public function getIndexActions($all = false)
    {
        $actions = collect($this->index_actions());
        if ($all) {
            $actions = collect($this->fields())->filter(function ($item) {
                return isset($item->actions) && count($item->actions) > 0;
            })->pluck('actions')->flatten(1)->merge($actions);
        }
        return $actions->map(function ($item) {
            if (is_string($item)) {
                return new $item();
            }
            return $item;
        })->filter(function ($item) use ($all) {
            if ($all) {
                return true;
            }
            return $item->show && $item->show_button;
        })->map(function ($item) {
            return $item->addData($this->data);
        });
    }

    public function getActions($all = false)
    {
        $actions = collect($this->actions());
        if ($all) {
            $actions = collect($this->fields())->filter(function ($item) {
                return isset($item->actions) && count($item->actions) > 0;
            })->pluck('actions')->flatten(1)->merge($actions);
        }
        return $actions->map(function ($item) {
            if (is_string($item)) {
                return new $item();
            }
            return $item;
        })->filter(function ($item) use ($all) {
            if ($all) {
                return true;
            }
            return $item->show && $item->show_button;
        })->map(function ($item) {
            return $item->addData($this->data);
        });
    }

    public function searchIndexAction($slug)
    {
        return collect($this->getIndexActions(true))->filter(function ($item) use ($slug) {
            return $item->slug == $slug;
        })->first();
    }

    public function searchAction($slug)
    {
        return collect($this->getActions(true))->filter(function ($item) use ($slug) {
            return $item->slug == $slug;
        })->first();
    }
}
