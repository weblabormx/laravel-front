<?php

namespace WeblaborMx\Front\Traits;

use Collective\Html\Eloquent\FormAccessible;

trait FrontModelCasting
{
    use FormAccessible {
        getFormValue as protected getOriginalFormValue;
    }

    public function getFormValue($key)
    {
        $value = $this->getOriginalFormValue($key);

        if (!is_scalar($value)) {
            return $this->getAttributeFromArray($key);
        }

        return $value;
    }
}
