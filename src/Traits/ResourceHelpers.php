<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Helpers\PartialIndex;

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
        return __($this->label);
    }

    public function getPartialIndexHelper($result, $page_name, $show_filters)
    {
        return new PartialIndex($this, $result, $page_name, $show_filters);
    }
}
