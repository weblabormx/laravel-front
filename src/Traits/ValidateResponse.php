<?php

namespace WeblaborMx\Front\Traits;

use Illuminate\Support\Str;

trait ValidateResponse
{
    public function isResponse($response)
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
}
