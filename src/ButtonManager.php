<?php

namespace WeblaborMx\Front;

use WeblaborMx\Front\Resource;
use WeblaborMx\Front\Texts\Button;

class ButtonManager
{
    public function getByName(string $name, ?Resource $front = null, mixed $object = null): Button
    {
        $config = config('front.buttons.' . $name);
        $extra = '';

        if ($name == 'delete') {
            $extra = "data-type='confirm' title='" . __('Delete') . "' data-info='" . __('Do you really want to remove this item?') . "' data-button-yes='" . __('Yes') . "' data-button-no='" . __('No') . "' data-action='" . url($front->getBaseUrl() . '/' . $object->getKey()) . "' data-redirection='" . url($front->getBaseUrl()) . "' data-variables='{ \"_method\": \"delete\", \"_token\": \"" . csrf_token() . "\" }'";
        }

        return Button::make($config['name'])
            ->setIcon($config['icon'])
            ->setExtra($extra)
            ->setType($config['type'])
            ->setClass($config['class']);
    }
}
