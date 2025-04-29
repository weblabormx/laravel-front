<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Str;

trait IsRunable
{
    public function run($object)
    {
        return $object->handle();
    }

    public function isFrontable($result)
    {
        if (!is_array($result)) {
            return false;
        }
        $result = collect($result)->map(function ($item) {
            return get_class($item);
        });
        return $result->contains(function ($item) {
            return Str::contains($item, 'WeblaborMx\Front');
        });
    }

    public function makeFrontable($result, $setters, $front)
    {
        $page_class = 'WeblaborMx\Front\Pages\Page';
        if (class_exists('App\Front\Pages\Page')) {
            $page_class = 'App\Front\Pages\Page';
        }

        // Get page
        $page = (new $page_class())->setSource('index')->setFields($result);
        foreach ($setters as $key => $value) {
            $page->$key = $value . ' - ' . __('Result');
        }

        // Get variables to pass
        $fields = $this->getParameters(compact('front', 'page'));

        return view($page->view, $fields);
    }

    /*
     * Controller Internal Functions
     */

    private function getObject($object)
    {
        $model = $this->front->getModel();
        $object = $model::find($object);
        if (!is_object($object)) {
            abort(404);
        }
        return $object;
    }

    private function getParameters($array = [], $object = false)
    {
        $parameters = request()->route()->parameters();
        $return = collect($parameters)->merge($array)->all();
        if ($object) {
            return (object) $return;
        }
        return $return;
    }

    public function getParameter($name = 'object')
    {
        return request()->route()->parameters()['front_' . $name];
    }
}
