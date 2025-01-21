<?php

namespace WeblaborMx\Front\Workers;

use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Jobs\FrontStore as Job;
use WeblaborMx\Front\Traits\IsRunable;

class FrontStore extends Worker
{
    use IsRunable;

    public $lense;

    public function handle()
    {
        $this->authorize('create', $this->front->getModel());
        $front = $this->front->setSource('store');
        if (isset($this->lense)) {
            $front = $front->getLense($this->lense);
        }
        return $this->run(new Job(request(), $front));
    }

    public function setLense($lense)
    {
        $this->lense = $lense;
        return $this;
    }
}
