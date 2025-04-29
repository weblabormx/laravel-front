<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Facades\Front;

trait HasBreadcrumbs
{
    public function breadcrumbs()
    {
        return [];
    }

    public function processBreadcrumbs($breadcrumbs)
    {
        return $breadcrumbs;
    }

    public function getBreadcrumbs($object = null, $action = null)
    {
        $breadcrumbs = $this->getBreadcrumbsValues($object, $action);
        $breadcrumbs = $this->processBreadcrumbs($breadcrumbs);

        // Update new fields as html
        $breadcrumbs = collect($breadcrumbs)->map(function ($item) {
            $item['html'] = '';
            if (isset($item['url'])) {
                $item['html'] .= '<a href="' . $item['url'] . '">';
            }
            $item['html'] .= $item['title'];
            if (isset($item['url'])) {
                $item['html'] .= '</a>';
            }
            return $item;
        });

        // Return values
        return $breadcrumbs;
    }

    public function getBreadcrumbsValues($object = null, $data = [])
    {
        $relation = $this->detectRelation();
        $front = is_null($relation['front']) ? $this : $relation['front']->setObject($relation['object']);

        // New format
        $breadcrumbs = collect($front->breadcrumbs())->map(function ($item, $key) {
            return [
                'title' => $item,
                'url' => $key
            ];
        })->values();

        // Index Action
        if ($front->source == 'create' && isset($data) && isset($data['action']) && !isset($object)) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            $breadcrumbs[] = ['title' => strip_tags($data['action']->title), 'active' => true];
            return $breadcrumbs;
        }

        // Action
        if ($front->source == 'create' && isset($data) && isset($data['action'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            if ($front->show_title) {
                $title = $front->title;
                $breadcrumbs[] = ['title' => $front->object->$title, 'url' => $front->getShowUrl()];
            }
            $breadcrumbs[] = ['title' => strip_tags($data['action']->title), 'active' => true];
            return $breadcrumbs;
        }

        // Massive
        if ($front->source == 'create' && isset($data) && isset($data['massive'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            if ($front->show_title) {
                $title = $front->title;
                $breadcrumbs[] = ['title' => $object->$title, 'url' => $front->getShowUrl($object)];
            }
            $breadcrumbs[] = ['title' => __('Edit') . ' ' . $data['massive']->title, 'active' => true];
            return $breadcrumbs;
        }

        // Index
        if ($front->source == 'index') {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
        }

        // Show normal
        if ($front->source == 'show' && is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            $title = $this->title;
            $breadcrumbs[] = ['title' => strip_tags($object->$title), 'active' => true];
        }

        // Show with relation
        if ($front->source == 'show' && !is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            $title = $front->title;
            $breadcrumbs[] = ['title' => $relation['object']->$title, 'url' => $front->getShowUrl()];
            $breadcrumbs[] = ['title' => $this->plural_label, 'url' => $this->getIndexUrl()];
            $title = $this->title;
            $breadcrumbs[] = ['title' => strip_tags($object->$title), 'active' => true];
        }

        // Create normal
        if ($front->source == 'create' && is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            $breadcrumbs[] = ['title' => __('Add new'), 'active' => true];
        }

        // Create with relation
        if ($front->source == 'create' && !is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            $title = $front->title;
            $breadcrumbs[] = ['title' => $relation['object']->$title, 'url' => $front->getShowUrl()];
            $breadcrumbs[] = ['title' => __('Add new') . ' ' . $this->label, 'active' => true];
        }

        // Edit normal
        if ($front->source == 'edit' && is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            if ($front->show_title) {
                $title = $front->title;
                $breadcrumbs[] = ['title' => $object->$title, 'url' => $front->getShowUrl()];
            }
            $breadcrumbs[] = ['title' => __('Edit'), 'active' => true];
        }

        // Edit with relation
        if ($front->source == 'edit' && !is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->getIndexUrl()];
            $title = $front->title;
            $breadcrumbs[] = ['title' => $relation['object']->$title, 'url' => $front->getShowUrl()];
            $breadcrumbs[] = ['title' => $this->plural_label, 'url' => $this->getIndexUrl()];
            $title = $this->title;
            $breadcrumbs[] = ['title' => strip_tags($object->$title), 'url' => $this->getShowUrl()];
            $title = $front->title;
            $breadcrumbs[] = ['title' => __('Edit') . ' ' . $this->$title, 'active' => true];
        }
        return $breadcrumbs;
    }

    private function detectRelation()
    {
        // Get relation information
        $front = null;
        $object = null;
        if (request()->filled('relation_front')) {
            $front = request()->relation_front;
            $front = str_replace('.', '\\', $front);
            $front = Front::makeResource($front, $this->source);
            $object = $front->getModel();
            $object = $object::find(request()->relation_id);
        }
        return compact('front', 'object');
    }

    public function getIndexUrl()
    {
        return $this->canIndex() ? $this->getBaseUrl() : '#';
    }

    public function getShowUrl($object = null)
    {
        if (is_null($object)) {
            $object = $this->object;
        }
        return $this->canShow() ? $this->getBaseUrl() . '/' . $object->getKey() : '#';
    }
}
