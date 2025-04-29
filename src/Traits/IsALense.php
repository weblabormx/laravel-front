<?php

namespace WeblaborMx\Front\Traits;

trait IsALense
{
    //$lense_slug;
    //$lense_title;
    //$lense_icon
    public $is_a_lense = true;

    public function getLenseSlug()
    {
        if (isset($this->lense_slug)) {
            return $this->lense_slug;
        }
        return class_basename(get_class($this));
    }
}
