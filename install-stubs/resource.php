<?php

namespace App\Front;

use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;

class {name} extends Resource
{
    public $base_url = '/admin/{slug}';
    public $model = 'App\{name}';
    public $title = 'id';

    public function fields()
    {
        return [
            ID::make(),
            Text::make('Name'),
        ];
    }
}
