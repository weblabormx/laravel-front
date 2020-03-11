<?php

namespace WeblaborMx\Front\Workers;

use WeblaborMx\Front\Front;
use WeblaborMx\Front\Jobs\StoreFront;

class FrontStore extends Worker
{
	public $lense;

	public function handle()
	{
		$this->authorize('create', $this->front->getModel());
        $front = $this->front->setSource('store');
        if(isset($this->lense)) {
			$front = $front->getLense($this->lense);
		}
        return StoreFront::dispatch(request(), $front);	
	}

	public function setLense($lense)
	{
		$this->lense = $lense;
		return $this;
	}
}
