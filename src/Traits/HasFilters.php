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
        $search_filter = config('front.default_search_filter');
        $filters = $this->filters();
        $filters[] = new $search_filter;
    	return collect($filters)->filter(function($item) {
            return $item->show;
        })->map(function($item) {
    		return $item->setResource($this);
    	});
    }
}
