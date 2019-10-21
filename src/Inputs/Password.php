<?php

namespace WeblaborMx\Front\Inputs;

class Password extends Input
{
	public function form()
	{
		return \Form::password($this->column, $this->attributes);
	}
}
