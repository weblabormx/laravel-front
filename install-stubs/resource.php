<?php

namespace {resources_folder}{model_extra_path};

use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;
use {model_folder}\{model} as Model;
use {resources_folder}\Resource;

class {model_name} extends Resource
{
    public $base_url = '{default_base_url}/{url}';
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
