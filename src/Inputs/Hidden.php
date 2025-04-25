<?php

namespace WeblaborMx\Front\Inputs;

class Hidden extends Input
{
	public $show_on_index = false;
	public $show_on_show = false;
	public $needs_to_be_on_panel = false;

	public function form()
	{
		$column_to_use = config('front.hidden_value');
		return \WeblaborMx\Front\Facades\Form::hidden($this->$column_to_use, $this->default_value, $this->attributes);
	}

	public function formHtml()
	{
		return $this->form();
	}
}
