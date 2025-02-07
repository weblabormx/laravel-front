<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Front;

class View extends Component
{
	private $with = [];
	public $needs_to_be_on_panel = false;

	public function form()
	{
		return view($this->column, $this->with)->render();
	}

	public function with($array)
	{
		$this->with = $array;
		return $this;
	}
}
