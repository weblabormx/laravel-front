<?php

namespace WeblaborMx\Front\Components;

class Welcome extends Component
{
	public function form()
	{
		return view('front::components.welcome')->render();
	}
}
