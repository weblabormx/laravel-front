<?php

namespace WeblaborMx\Front\Traits;

trait HasBreadcrumbs
{
	public function breadcrumbs()
    {
        return [];
    }

    public function getBreadcrumbs($object = null, $action = null)
    {
        $breadcrumbs = $this->getBreadcrumbsValues($object, $action);
        $breadcrumbs = collect($breadcrumbs)->map(function($item) {
            $item['html'] = '';
            if(isset($item['url'])) {
                $item['html'] .= '<a href="'.$item['url'].'">';
            }
            $item['html'] .= $item['title'];
            if(isset($item['url'])) {
                $item['html'] .= '</a>';
            }
            return $item;
        });
    	return $breadcrumbs;
    }

    public function getBreadcrumbsValues($object = null, $data = [])
    {
        $relation = $this->detectRelation();
        $front = is_null($relation['front']) ? $this : $relation['front'];

        // New format
        $breadcrumbs = collect($front->breadcrumbs())->map(function($item, $key) {
            return [
                'title' => $item,
                'link' => $key
            ];
        });

        // Index Action
        if($front->source=='create' && isset($data) && isset($data['action']) && !isset($object)) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            $breadcrumbs[] = ['title' => strip_tags($action->title), 'active' => true];
            return $breadcrumbs;
        }

        // Action
        if($front->source=='create' && isset($data) && isset($data['action'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            if($front->show_title) {
                $title = $front->title;
                $breadcrumbs[] = ['title' => $front->$title, 'url' => $front->base_url.'/'.$front->object->getKey()];
            }
            $breadcrumbs[] = ['title' => strip_tags($action->title), 'active' => true];
            return $breadcrumbs;
        }

        // Massive
        if($front->source=='create' && isset($data) && isset($data['massive'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            if($front->show_title) {
                $title = $front->title;
                $breadcrumbs[] = ['title' => $object->$title, 'url' => $front->base_url.'/'.$object->getKey()];
            }
            $breadcrumbs[] = ['title' => __('Edit').' '.$data['massive']->title, 'active' => true];
            return $breadcrumbs;
        }

        // Index
        if($front->source=='index') {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
        }

        // Show normal
        if($front->source=='show' && is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            $title = $this->title;
            $breadcrumbs[] = ['title' => strip_tags($object->$title), 'active' => true];
        }

        // Show with relation
        if($front->source=='show' && !is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            $title = $front->title;
            $breadcrumbs[] = ['title' => $relation['object']->$title, 'url' => $front->base_url.'/'.$relation['object']->getKey()];
            $breadcrumbs[] = ['title' => $this->plural_label];
            $title = $this->title;
            $breadcrumbs[] = ['title' => strip_tags($object->$title), 'active' => true];
        }

        // Create normal
        if($front->source=='create' && is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            $breadcrumbs[] = ['title' => __('Add new'), 'active' => true];
        }

        // Create with relation
        if($front->source=='create' && !is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            $title = $front->title;
            $breadcrumbs[] = ['title' => $relation['object']->$title, 'url' => $front->base_url.'/'.$relation['object']->getKey()];
            $breadcrumbs[] = ['title' => __('Add new').' '.$this->label, 'active' => true];
        }

        // Edit normal
        if($front->source=='edit' && is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            if($front->show_title) {
                $title = $front->title;
                $breadcrumbs[] = ['title' => $object->$title, 'url' => $front->base_url.'/'.$front->object->getKey()];
            }
            $breadcrumbs[] = ['title' => __('Edit'), 'active' => true];
        }

        // Edit with relation
        if($front->source=='edit' && !is_null($relation['front'])) {
            $breadcrumbs[] = ['title' => $front->plural_label, 'url' => $front->base_url];
            $title = $front->title;
            $breadcrumbs[] = ['title' => $relation['object']->$title, 'url' => $front->base_url.'/'.$relation['object']->getKey()];
            $breadcrumbs[] = ['title' => $this->plural_label];
            $title = $front->title;
            $breadcrumbs[] = ['title' => __('Edit').' '.$this->$title, 'active' => true];
        }
        return $breadcrumbs;
    }

    private function detectRelation()
    {
    	// Get relation information
        $front = null;
        $object = null;
        if(request()->filled('relation_front')) {
            $front = request()->relation_front;
            $front = 'App\Front\\'.$front;
            $front = new $front($this->source);
            $object = $front->getModel();
            $object = $object::find(request()->relation_id);
        }
        return compact('front', 'object');
    }
}
