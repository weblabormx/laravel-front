<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Source;

trait Sourceable
{
    /** 
     * @var string|null
     * @deprecated v2.1.35 
     */
    public $source = null;

    /** @var Source */
    private $sourceClass;

    /** @return Source */
    public function source()
    {
        $source = $this->source;

        if (is_null($source)) {
            return null;
        }

        if (!isset($this->sourceClass)) {
            $this->sourceClass = new Source($source);
        }

        return $this->sourceClass;
    }

    public function setSource($source)
    {
        $this->source = $source;
        session()->put('source', $source, now()->addMinute());
        return $this;
    }
}
