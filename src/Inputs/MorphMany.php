<?php

namespace WeblaborMx\Front\Inputs;

class MorphMany extends HasMany
{
    public $morph_column;

    public function getBaseUrl($resource, $relation_function)
    {
        $this->morph_column = $relation_function->getMorphType();
        return $this->column.'='.$resource->object->getKey().'&'.$this->morph_column.'='.$relation_function->getMorphClass();
    }

    public function getColumnsToHide()
    {
        return [$this->column, $this->morph_column];
    }
}
