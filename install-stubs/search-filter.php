<?php

namespace App\Front\Filters;

use WeblaborMx\Front\Inputs\Hidden;

class SearchFilter extends Filter
{
    public $slug = 'search';

    public function apply($query, $value)
    {
        return $query->search($value);
    }

    public function field()
    {
        return Hidden::make('Search');
    }

}
