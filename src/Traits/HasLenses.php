<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Str;

trait HasLenses
{
    private $normal_front;

    public function lenses()
    {
        return [];
    }

    public function getLense($slug)
    {
        // Can pass the class directly
        if (Str::contains($slug, '\\')) {
            $object = new $slug();
            return $object->addData($this->data)->setModel($this->getModel())->setSource($this->source);
        }
        // Or the slug name
        return collect($this->lenses())->filter(function ($item) use ($slug) {
            return $item->getLenseSlug() == $slug;
        })->map(function ($item) {
            return $item->addData($this->data)->setModel($this->getModel())->setSource($this->source)->setNormalFront($this);
        })->first();
    }

    public function setNormalFront($front)
    {
        $this->normal_front = $front;
        return $this;
    }
}
