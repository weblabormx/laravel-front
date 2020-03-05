<?php

namespace WeblaborMx\Front\Components;

class Title extends Component
{
	public $size = 2;

	public function form()
	{
		$component = $this;
		return view('front::components.title', compact('component'))->render();
	}

	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}
}
