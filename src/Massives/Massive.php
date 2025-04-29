<?php

namespace WeblaborMx\Front\Massives;

class Massive
{
    /**
     * If new rows available is true it will be able to add new rows
     **/

    public $new_rows_available = true;

    /**
     * Add buttons to be shown on edit view
     * Example: ['saveAndUpdateTeamStats' => 'Save and Update Team Stats']
     * When clicking the button will execute saveAndUpdateTeamStats() function
     **/

    public $buttons = [];

    /**
     * If you want to modify the request data gotten just update it with this funciton
     **/

    public function processData($request)
    {
        return $request;
    }
}
