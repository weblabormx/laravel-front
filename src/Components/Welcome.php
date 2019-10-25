<?php

namespace WeblaborMx\Front\Components;

class Welcome extends Component
{
	public function form()
	{
		$component = $this;
		return view('front::components.welcome', compact('component'))->render();
	}
}
