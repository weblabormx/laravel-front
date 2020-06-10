<?php

namespace WeblaborMx\Front\Traits;

trait IsALense
{
	public $lense_slug = null;
	public $is_a_lense = true;

	public function getLenseSlug()
    {
    	if(isset($this->lense_slug)) {
    		return $lense_slug;
    	}
        return class_basename(get_class($this));
    }
}
