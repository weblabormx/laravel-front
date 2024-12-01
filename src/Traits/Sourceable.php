<?php

namespace WeblaborMx\Front\Traits;

trait Sourceable
{
    public $source;

    public function setSource($source)
    {
        $this->source = $source;
        session()->put('source', $source, now()->addMinute());
        return $this;
    }
}
