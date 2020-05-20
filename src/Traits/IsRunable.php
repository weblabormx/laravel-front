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

    public function makeFrontable($result)
    {
        $page = new Page;
        $page->setFields($result);
        dd($page->getFields());
        $front = $this->getFront();
        $fields = $this->getFields(compact('front'));

        // Search the individual action
        if(isset($fields['front_object'])) {
            $fields['action'] = $front->searchAction($fields['action']);
        } else {
            $fields['action'] = $front->searchIndexAction($fields['action']);
        }
        dd($fields);
        return view('front::crud.action', $fields);
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
}
