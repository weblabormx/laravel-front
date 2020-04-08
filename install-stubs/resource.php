<?php

namespace App\Front;

use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;
use {model_folder}\{name} as Model;

class {name} extends Resource
{
    public $base_url = '{default_base_url}/{slug}';
    public $model = Model::class;
    public $title = 'id';

    public function fields()
    {
        return [
            ID::make(),
            Text::make('Name'),
        ];
    }
}
