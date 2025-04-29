<?php

namespace WeblaborMx\Front\Http\Controllers;

use WeblaborMx\Front\Facades\Front;

class PageController extends Controller
{
    public function page($page, $action)
    {
        // Call page class
        $page_class = Front::resolvePage($page);
        $page = (new $page_class())->setSource('index');
        if ($action != 'get') {
            $page = $page->changeFieldsFunction($action);
        }
        $method = 'execute' . ucfirst($action);
        return $page->$method(compact('page', 'action'));
    }
}
