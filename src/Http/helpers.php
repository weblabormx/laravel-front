<?php

use WeblaborMx\Front\Texts\Button;
use Illuminate\Support\Str;

function getThumb($full_name, $prefix, $force = false)
{
    $editGetThumbHelper = 'editGetThumb';
    $validateGetThumbHelper = 'validateGetThumb';

    if (function_exists($validateGetThumbHelper) && !$force) {
        $execute = $validateGetThumbHelper($full_name);
        if (!$execute) {
            if (function_exists($editGetThumbHelper)) {
                return $editGetThumbHelper($full_name);
            }
            return $full_name;
        }
    }
    $full_name = explode('/', $full_name);
    $key = count($full_name) - 1;

    $name = explode('.', $full_name[$key]);
    $name[0] = $name[0] . $prefix;
    $name = implode('.', $name);

    $full_name[$key] = $name;
    $full_name = implode('/', $full_name);

    if (function_exists($editGetThumbHelper)) {
        return $editGetThumbHelper($full_name);
    }

    return $full_name;
}

function getFront($model, $source = null)
{
    $model = config('front.resources_folder') . '\\' . $model;
    return new $model($source);
}

function getFrontByModel($object)
{
    $class = $object->getMorphClass();
    $class = str_replace('App\\Models\\', '', $class);
    return getFront($class)->setObject($object);
}

function getButtonByName($name, $front = null, $object = null)
{
    $config = config('front.buttons.' . $name);
    $extra = '';
    if ($name == 'delete') {
        $object_url = url($front->getBaseUrl() . '/' . $object->getKey());
        $redirection = url()->full();
        if(Str::contains($redirection, $object_url)) {
            $redirection = url($front->getBaseUrl());
        }
        $extra = "data-type='confirm' title='" . __('Delete') . "' data-info='" . __('Do you really want to remove this item?') . "' data-button-yes='" . __('Yes') . "' data-button-no='" . __('No') . "' data-action='" . $object_url . "' data-redirection='" . $redirection . "' data-variables='{ \"_method\": \"delete\", \"_token\": \"" . csrf_token() . "\" }'";
    }
    return Button::make($config['name'])
        ->setIcon($config['icon'])
        ->setExtra($extra)
        ->setType($config['type'])
        ->setClass($config['class']);
}

function isResponse($response)
{
    if (!is_object($response)) {
        return false;
    }
    $class = get_class($response);
    $classes = [$class];
    while (true) {
        $class = get_parent_class($class);
        if (!$class) {
            break;
        }
        $classes[] = $class;
    }
    return collect($classes)->contains(function ($item) {
        return Str::contains($item, [
            'Symfony\Component\HttpFoundation\Response',
            'Illuminate\View\View'
        ]);
    });
}