<?php

namespace WeblaborMx\Front\Pages;

use WeblaborMx\Front\Traits\HasInputs;
use WeblaborMx\Front\Traits\HasLinks;
use WeblaborMx\Front\Traits\HasBreadcrumbs;
use WeblaborMx\Front\Traits\Sourceable;

class Page
{
	use HasInputs, HasLinks, HasBreadcrumbs, Sourceable;

	public $title;
	public $layout = 'layouts.app';
	public $has_big_card = true;

	public function __construct()
	{
		if(!isset($this->title)) {
			$title = class_basename(get_class($this));
			$this->title = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $title);
		}
	}

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
}
