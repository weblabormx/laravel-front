<?php

namespace WeblaborMx\Front\Pages;

use WeblaborMx\Front\Traits\HasInputs;
use WeblaborMx\Front\Traits\HasLinks;
use WeblaborMx\Front\Traits\HasBreadcrumbs;
use WeblaborMx\Front\Traits\Sourceable;
use WeblaborMx\Front\Traits\IsRunable;

class Page
{
    use HasInputs;
    use HasLinks;
    use HasBreadcrumbs;
    use Sourceable;
    use IsRunable;

    public $title;
    public $layout;
    public $view = 'front::page';
    public $has_big_card = true;
    public $route;

    public function __construct()
    {
        $this->route = $this->getParameters([], true);
        if (!isset($this->title)) {
            $title = class_basename(get_class($this));
            $this->title = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $title);
        }
        $this->load();
    }

    public function load()
    {
        //
    }

    /*
    * Customizable methods
    */

    public function style()
    {
        return;
    }

    public function post()
    {
        return;
    }

    public function put()
    {
        return;
    }

    public function delete()
    {
        return;
    }

    /*
    * Executing methods
    */

    public function executeGet($data)
    {
        return view($this->view, $this->getParameters($data));
    }

	public function executePost($data)
	{
		$return = $this->post();
		if (isResponse($return)) {
			return $return;
		}
		flash(__('Saved successfully'))->success();
		return back();
	}

	public function executePut($data)
	{
		$return = $this->post();
		if (isResponse($return)) {
			return $return;
		}
		flash(__('Updated successfully'))->success();
		return back();
	}

	public function executeDelete($data)
	{
		$return = $this->delete();
		if (isResponse($return)) {
			return $return;
		}
		flash(__('Deleted successfully'))->success();
		return back();
	}
}
