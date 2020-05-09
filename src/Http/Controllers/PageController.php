<?php

namespace WeblaborMx\Front\Http\Controllers;

use WeblaborMx\Front\Http\Repositories\FrontRepository;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function page($page, $action = null)
    {
        // Call page class
        $page_class = 'App\Front\Pages\\'.$page;
        $page = (new $page_class)->setSource('index');
        if(!is_null($action)) {
        	$page = $page->changeFieldsFunction($action);
        }
        return view($page->view, $this->getFields(compact('page', 'action')));
    }

    private function getFields($array)
    {
        $parameters = request()->route()->parameters();
        return collect($array)->merge($parameters)->all();
    }
}
