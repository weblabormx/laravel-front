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

    'resources_folder' => 'App\Front\Resources',

    /*
    |--------------------------------------------------------------------------
    | Model's Folder
    |--------------------------------------------------------------------------
    |
    | This is the folder location where the models are located, used when
    | creating resources with the commands.
    |
    */

    'models_folder' => 'App\Models',

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
    | Default Date Format
    |--------------------------------------------------------------------------
    |
    | The default date format used on the system
    |
    */

    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',

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
            'icon' => 'eye',
            'name' => 'See',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'edit' => [
            'icon' => 'pencil',
            'name' => 'Edit',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'create' => [
            'icon' => 'plus-small',
            'name' => 'Create',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'delete' => [
            'icon' => 'trash',
            'name' => 'Delete',
            'type' => 'btn-outline-danger',
            'class' => ''
        ],
        'up' => [
            'icon' => 'arrow-up',
            'name' => 'Up',
            'type' => 'btn-primary',
            'class' => ''
        ],
        'down' => [
            'icon' => 'arrow-down',
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
        'class' => 'mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm'
    ],

    /* 
    |--------------------------------------------------------------------------
    | Datetime Wrap
    |--------------------------------------------------------------------------
    |
    | If you want to wrap the datetime inputs on a carbon macro
    | This is useful if your user have a custom timezone
    |
    */

    'datetime_wrap' => false,

    /*
    |--------------------------------------------------------------------------
    | Default Thumbnails
    |--------------------------------------------------------------------------
    |
    | The default thumbnails used on the system, you can add more
    | 
    */

    'thumbnails' => [
        ['prefix' => 's', 'width' => 90,   'height' => 90,   'fit' => true],  // Small Square
        ['prefix' => 'b', 'width' => 160,  'height' => 160,  'fit' => true],  // Big Square
        ['prefix' => 't', 'width' => 160,  'height' => 160,  'fit' => false], // Small Thumbnail
        ['prefix' => 'm', 'width' => 320,  'height' => 320,  'fit' => false], // Medium Thumbnail
        ['prefix' => 'l', 'width' => 640,  'height' => 640,  'fit' => false], // Large Thumbnail
        ['prefix' => 'h', 'width' => 1024, 'height' => 1024, 'fit' => false], // Huge Thumbnail
    ]

];
