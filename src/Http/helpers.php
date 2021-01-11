<?php

function getThumb($full_name, $prefix, $force = false)
{
    if(function_exists('validateGetThumb') && !$force) {
        $execute = validateGetThumb($full_name);
        if(!$execute) {
            return $full_name;
        }
    }
    $full_name = explode('/', $full_name);
    $key = count($full_name)-1;

    $name = explode('.', $full_name[$key]);
    $name[0] = $name[0].$prefix;
    $name = implode('.', $name);
    
    $full_name[$key] = $name;
    $full_name = implode('/', $full_name);

    return $full_name;
}

function getFront($model, $source = null)
{
	$model = config('front.resources_folder').'\\'.$model;
    return new $model($source);
}
