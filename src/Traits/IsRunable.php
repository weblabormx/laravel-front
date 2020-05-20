<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Str;
use WeblaborMx\Front\Pages\Page;

trait IsRunable
{
    public function run($object)
    {
        return $object->handle();
    }

    public function isResponse($response)
    {
        if(!is_object($response)) {
            return false;
        }
    	$class = get_class($response);
    	$classes = [$class];
    	while (true) {
    		$class = get_parent_class($class);
    		if(!$class) {
    			break;
    		}
    		$classes[] = $class;
    	}
    	return collect($classes)->contains(function($item) {
            return Str::contains($item, [
                'Symfony\Component\HttpFoundation\Response',
                'Illuminate\View\View'
            ]);
        });
    }

    public function isFrontable($result)
    {
        if(!is_array($result)) {
            return false;
        }
        $result = collect($result)->map(function($item) {
            return get_class($item);
        });
        return $result->contains(function($item) {
            return Str::contains($item, 'WeblaborMx\Front');
        });
    }

    public function makeFrontable($result, $setters)
    {
        // Get page
        $page = (new Page)->setSource('index')->setFields($result);
        foreach ($setters as $key => $value) {
            $page->$key = $value.' - '.__('Result');
        }

        // Get variables to pass
        $front = $this->getFront();
        $fields = $this->getFields(compact('front', 'page'));

        return view($page->view, $fields);
    }

    /*
     * Controller Internal Functions
     */

    private function getFront()
    {
        if(request()->route()==null) {{
            return;
        }}
        $action = request()->route()->getAction();
        if(!is_array($action) || !isset($action['prefix'])) {
            return;
        }
        $action = explode('/', $action['prefix']);
        $action = $action[count($action)-1];
        $action = Str::camel(Str::singular($action));
        $action = ucfirst($action);
        $class = 'App\Front\\'.$action;
        return new $class;
    }

    private function getObject($object)
    {
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }
        return $object;
    }

    private function getFields($array)
    {
        $parameters = request()->route()->parameters();
        return collect($parameters)->merge($array)->all();
    }

    private function getParameter($name = 'object')
    {
        return request()->route()->parameters()['front_'.$name];
    }
}
