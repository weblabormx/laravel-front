<?php

namespace WeblaborMx\Front\Workers;

use WeblaborMx\Front\Front;
use WeblaborMx\Front\Jobs\StoreFront;

class FrontStore extends Worker
{
	public function handle()
	{
		$this->authorize('create', $this->front->getModel());
        $front = $this->front->setSource('store');
        return StoreFront::dispatch(request(), $front);	
	}
}
