<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Front;

class View extends Component
{
	private $compact = [];

	public function formHtml()
	{
		return view($this->column, $this->compact);
	}

	public function showHtml($object)
	{
		return view($this->column, $this->compact);
	}

	public function compact($array)
	{
		$this->compact = $array;
		return $this;
	}
}
