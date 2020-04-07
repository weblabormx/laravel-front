<?php

namespace App\Front\Filters;

use WeblaborMx\Front\Inputs\Text;

class {name} extends Filter
{
    public $slug = 'column';

    public function apply($query, $value)
    {
        return $query->where('column', $value);
    }

    public function field()
    {
        return Text::make('Column');
    }
}