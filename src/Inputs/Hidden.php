<?php

namespace WeblaborMx\Front\Inputs;

class Hidden extends Input
{
	public $show_on_index = false;
	public $show_on_show = false;

	public function form()
	{
		return \Form::hidden($this->column, $this->default_value, $this->attributes);
	}

	public function formHtml()
	{
		return $this->form();
	}
}
