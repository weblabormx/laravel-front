<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Front's resources Folder
    |--------------------------------------------------------------------------
    |
    | This is the folder location where the front resources are located
    |
    */

    'resources_folder' => 'App\Front',

    /*
    |--------------------------------------------------------------------------
    | Model's Folder
    |--------------------------------------------------------------------------
    |
    | This is the folder location where the models are located, used when
    | creating resources with the commands.
    |
    */

    'models_folder' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Default Base Url
    |--------------------------------------------------------------------------
    |
    | This value is used when creating a new resource, it will add it automatically
    | on the base_url attribute
    |
    */

    'default_base_url' => '/admin',

    /*
    |--------------------------------------------------------------------------
    | Default Search Filter
    |--------------------------------------------------------------------------
    |
    | The Apply() function on this filter will be used when making a search
    | for a Front Resource. Used for autocomplete inputs and when adding
    | searchable() to relationship functions.
    |
    */

    'default_search_filter' => App\Front\Filters\SearchFilter::class,

    /*
    |--------------------------------------------------------------------------
    | Default Layout Name
    |--------------------------------------------------------------------------
    |
    | The default layout name that uses Laravel Front
    |
    */

    'default_layout' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Hidden default value
    |--------------------------------------------------------------------------
    |
    | If you want to get the exactly value added on Hidden::make('The Value') set "title" (In this case it will use the column 'The Value')
    | Otherwise put "value" to convert to lower case and Snake case, example: "the_value" instead
    |
    */

    'hidden_value' => 'column',

    /*
    |--------------------------------------------------------------------------
    | Default Buttons
    |--------------------------------------------------------------------------
    |
    | Buttons used on the system by default
    |
    */

    'buttons' => [
        'show' => [
            'icon' => 'fa fa-eye',
            'name' => 'See',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'edit' => [
            'icon' => 'fa fa-edit',
            'name' => 'Edit',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'create' => [
            'icon' => 'fa fa-plus',
            'name' => 'Create',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'delete' => [
            'icon' => 'fa fa-times pr-2',
            'name' => 'Delete',
            'type' => 'btn-danger',
            'class' => ''
        ],
        'up' => [
            'icon' => 'fa fa-arrow-up',
            'name' => 'Up',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'down' => [
            'icon' => 'fa fa-arrow-down',
            'name' => 'Down',
            'type' => 'btn-primary',
            'class' => ''
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Input Attributes
    |--------------------------------------------------------------------------
    |
    | Change here in case you want to change the default attributes for the inputs
    |
    */

    'default_input_attributes' => [
        'class' => 'form-control'
    ]

];
