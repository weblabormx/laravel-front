<?php

namespace WeblaborMx\Front\Massives;

use App\Front\Resource;
use Illuminate\Support\Str;

class Massive
{
	/**
	 * Add buttons to be shown on edit view
	 * Example: ['saveAndUpdateTeamStats' => 'Save and Update Team Stats']
	 * When clicking the button will execute saveAndUpdateTeamStats() function
	 **/

	public $buttons = [];

	/**
	 * Add buttons to be shown on index view
	 * Example: ['updateData' => 'Update Data']
	 * When clicking the button will execute updateData() function
	 **/
	
	public $index_buttons = [];

	/**
	 * If you want to modify the request data gotten just update it with this funciton
	 **/
	
	public function editRequest($request)
	{
		return $request;
	}
}
