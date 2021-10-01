<?php

namespace WeblaborMx\Front\Texts;

class Button extends Text
{
	public $link  = '#';
	public $style = '';
	public $extra = '';
	public $class = '';
	public $type = 'btn-primary';

	public function load()
	{
		$this->text = $this->title;
	}

	public function form()
	{
		$button = $this;
		return view('front::texts.button', compact('button'))->render();
	}

	public function addLink($link)
	{
		$this->link = $link;
		return $this;
	}

	public function setExtra($extra)
	{
		$this->extra = $extra;
		return $this;
	}

	public function setStyle($style)
	{
		$this->style = $style;
		return $this;
	}

	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
}
