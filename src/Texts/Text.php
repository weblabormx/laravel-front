<?php

namespace WeblaborMx\Front\Texts;

use WeblaborMx\Front\Inputs\Input;
use WeblaborMx\Front\Traits\WithWidth;

class Text extends Input
{
	use WithWidth;

	public $is_input = false;
	public $text;

	public function formHtml()
	{
		return $this->form();
	}

	public function showHtml($object)
	{
		return $this->formHtml();
	}
	
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}
}
