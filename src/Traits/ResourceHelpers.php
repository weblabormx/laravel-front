<?php

namespace WeblaborMx\Front\Traits;

trait ResourceHelpers
{
	public function getTitle($object)
    {
        if($this->show_title && $this->source=='show') {
            $view_title_field = $this->view_title;
            return $object->$view_title_field;
        }
        if($this->show_title) {
            $view_title_field = $this->title;
            return $object->$view_title_field;
        }
        return $this->plural_label;
    }
}
