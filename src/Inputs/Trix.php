<?php

namespace WeblaborMx\Front\Inputs;

class Trix extends Input
{
	public $show_on_index = false;
	
	public function form()
	{
		$this->attributes['data-type'] = 'wysiwyg';
		return \Form::textarea($this->column, $this->default_value, $this->attributes);
	}
}
