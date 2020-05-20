<?php

namespace WeblaborMx\Front\Http\Controllers;

use WeblaborMx\Front\Http\Repositories\FrontRepository;
use WeblaborMx\Front\Traits\IsRunable;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use IsRunable;

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
}
