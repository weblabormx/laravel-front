<?php

namespace WeblaborMx\Front\Traits;

trait HasFilters
{
    public $default_search_filter;

    public function filters()
    {
        return [];
    }

    public function cachedFilters()
    {
        return cache()->store('array')->rememberForever('filters_' . static::class, function () {
            return $this->filters();
        });
    }

    public function visibleFilters()
    {
        return collect($this->cachedFilters())->where('visible', true);
    }

    public function getFilters()
    {
        $search_filter = $this->getDefaultSearchFilter();
        $filters = $this->cachedFilters();
        $filters[] = new $search_filter();
        return collect($filters)->filter(function ($item) {
            return $item->show;
        })->map(function ($item) {
            return $item->setResource($this);
        });
    }

    public function getFilterInputs()
    {
        return $this->getFilters()->map(function ($item) {
            return $item->field();
        })->flatten();
    }

    public function hasFilters()
    {
        return $this->getFilterInputs()->filter(function($item) {
            return $item->show_on_filter;
        })->count() > 0;
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
