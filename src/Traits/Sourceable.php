<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Source;

trait Sourceable
{
    /** 
     * @var string|null
     * @deprecated 2.3.0 Use `source()` method instead.
     */
    public $source = null;

    /** 
     * @var \WeblaborMx\Front\Source
     * @internal The singleton`Source` instance, obtain it with `source()` 
     */
    private $_sourceClass;

    /** @return Source|null */
    public function source()
    {
        $source = $this->source;

        if (is_null($source)) {
            return null;
        }

        if (!isset($this->_sourceClass)) {
            $this->_sourceClass = app()->make(Source::class, compact('source'));
        }

        return $this->_sourceClass;
    }

    public function setSource($source)
    {
        $this->source = $source;
        session()->put('source', $source, now()->addMinute());
        return $this;
    }
}
