<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Front;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use WeblaborMx\Front\Jobs\StoreFront;

class FrontStore extends Component
{
	use AuthorizesRequests;

	public $front;
	public $query;
	public $lense;
	public $base_url;

	public function __construct($front, $column = null, $extra = null, $source = null)
	{
		$front = '\App\Front\\'.$front;
		$this->source = $source;
		$this->front = new $front($this->source);
	}

	public static function make($title = null, $column = null, $extra = null) 
	{
		$object = parent::make($title, $column, $extra);
		return $object->handle();
	}

	public function handle()
	{
		try {
			$this->authorize('create', $this->front->getModel());
		} catch (\Exception $e) {
			return $e->getMessage();
		}

        $front = $this->front->setSource('store');
        
        try {
        	return StoreFront::dispatch(request(), $front);	
        } catch (\Exception $e) {
        	return collect($e->errors())->flatten(1)->implode(' ');
        }
	}
}
