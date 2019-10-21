<?php

namespace WeblaborMx\Front\Http\Repositories;

use App\Models\TSFSport;
use Illuminate\Support\Str;

class FrontRepository 
{
    public function index($front)
    {
        $object = $front->globalIndexQuery();

        // Detect if crud is just for 1 item
        if(!Str::contains(get_class($object), 'Illuminate\Database\Eloquent')) {
            $url = $front->base_url.'/'.$object->getKey();
            return redirect($url);
        }
        return $object->paginate($front->pagination);
    }

    public function getIndexAction($slug, $front)
    {
        return collect($front->getIndexActions(true))->filter(function($item) use ($slug) {
            return $item->slug == $slug;
        })->first();
    }

    public function getAction($slug, $front)
    {
        return collect($front->getActions(true))->filter(function($item) use ($slug) {
            return $item->slug == $slug;
        })->first();
    }
}
