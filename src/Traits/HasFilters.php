<?php

namespace WeblaborMx\Front\Traits;

trait HasFilters
{
	public function filters()
    {
        return [];
    }

    public function getFilters()
    {
    	return collect($this->filters())->filter(function($item) {
            return $item->show;
        })->map(function($item) {
    		return $item->setResource($this);
    	});
    }
}
