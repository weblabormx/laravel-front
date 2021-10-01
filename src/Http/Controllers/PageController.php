<?php

namespace WeblaborMx\Front\Http\Controllers;

class PageController extends Controller
{
    public function page($page, $action)
    {
        // Call page class
        $page_class = 'App\Front\Pages\\'.$page;
        $page = (new $page_class)->setSource('index');
        if($action!='get') {
        	$page = $page->changeFieldsFunction($action);
        }
        $method = 'execute'.ucfirst($action);
        return $page->$method(compact('page', 'action'));
    }
}
