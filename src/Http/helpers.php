<?php

use WeblaborMx\Front\Texts\Button;

function getThumb($full_name, $prefix, $force = false)
{
    if (function_exists('validateGetThumb') && !$force) {
        $execute = validateGetThumb($full_name);
        if (!$execute) {
            if (function_exists('editGetThumb')) {
                return editGetThumb($full_name);
            }
            return $full_name;
        }
    }
    $full_name = explode('/', $full_name);
    $key = count($full_name) - 1;

    $name = explode('.', $full_name[$key]);
    $name[0] = $name[0].$prefix;
    $name = implode('.', $name);

    $full_name[$key] = $name;
    $full_name = implode('/', $full_name);

    if (function_exists('editGetThumb')) {
        return editGetThumb($full_name);
    }

    return $full_name;
}

function getFront($model, $source = null)
{
    $model = config('front.resources_folder').'\\'.$model;
    return new $model($source);
}

function getButtonByName($name, $front = null, $object = null)
{
    $config = config('front.buttons.'.$name);
    $extra = '';
    if ($name == 'delete') {
        $extra = "data-type='confirm' title='".__('Delete')."' data-info='".__('Do you really want to remove this item?')."' data-button-yes='".__('Yes')."' data-button-no='".__('No')."' data-action='".url($front->getBaseUrl().'/'.$object->getKey())."' data-redirection='".url($front->getBaseUrl())."' data-variables='{ \"_method\": \"delete\", \"_token\": \"".csrf_token()."\" }'";
    }
    return Button::make($config['name'])
        ->setIcon($config['icon'])
        ->setExtra($extra)
        ->setType($config['type'])
        ->setClass($config['class']);
}
