<?php

public function getThumb($full_name, $prefix)
{
    $full_name = explode('/', $full_name);
    $key = count($full_name)-1;

    $name = explode('.', $full_name[$key]);
    $name[0] = $name[0].$prefix;
    $name = implode('.', $name);
    
    $full_name[$key] = $name;
    $full_name = implode('/', $full_name);

    return $full_name;
}