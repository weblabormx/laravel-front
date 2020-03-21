<?php

namespace WeblaborMx\Front\Traits;

trait IsRunable
{
    public function run($object)
    {
        return $object->handle();
    }

    public function isResponse($response)
    {
    	$class = get_class($response);
    	$classes = [$class];
    	while (true) {
    		$class = get_parent_class($class);
    		if(!$class) {
    			break;
    		}
    		$classes[] = $class;
    	}
    	return collect($classes)->contains('Symfony\Component\HttpFoundation\Response');
    }
}
