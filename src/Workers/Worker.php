<?php

namespace WeblaborMx\Front\Workers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Worker
{
	use AuthorizesRequests;

	public $is_input = false;
	public $front;

	public function __construct($front, $column = null, $extra = null, $source = null)
	{
		$front = '\App\Front\\'.$front;
		$this->source = $source;
		$this->front = new $front($this->source);
	}

	public static function make($title = null, $column = null, $extra = null) 
	{
		$object = parent::make($title, $column, $extra);
		try {
			return $object->handle();
		} catch (\Exception $e) {
			return $e->getMessage();
		} catch (\Exception $e) {
        	return collect($e->errors())->flatten(1)->implode(' ');
        }
		
	}
}
