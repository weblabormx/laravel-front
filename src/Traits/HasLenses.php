<?php

namespace WeblaborMx\Front\Traits;

trait HasLenses
{
	public function lenses()
    {
        return [];
    }

    public function getLense($slug)
    {
    	return collect($this->lenses())->filter(function($item) use ($slug) {
    		return $item->getLenseSlug() == $slug;
    	})->map(function($item) {
    		return $item->addData($this->data)->setModel($this->getModel())->setSource($this->source);
    	})->first();
    }
}
