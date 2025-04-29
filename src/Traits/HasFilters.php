<?php

namespace WeblaborMx\Front\Traits;

trait HasFilters
{
    public $default_search_filter;

    public function filters()
    {
        return [];
    }

    public function visibleFilters()
    {
        return collect($this->filters())->where('visible', true);
    }

    public function getFilters()
    {
        $search_filter = $this->getDefaultSearchFilter();
        $filters = $this->filters();
        $filters[] = new $search_filter();
        return collect($filters)->filter(function ($item) {
            return $item->show;
        })->map(function ($item) {
            return $item->setResource($this);
        });
    }

    public function getDefaultSearchFilter()
    {
        $default = $this->default_search_filter;
        if (!is_null($default)) {
            return $default;
        }
        return config('front.default_search_filter');
    }
}
