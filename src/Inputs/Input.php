<?php

namespace WeblaborMx\Front\Inputs;

use WeblaborMx\Front\Front;
use WeblaborMx\Front\Traits\InputVisibility;
use WeblaborMx\Front\Traits\InputSetters;
use WeblaborMx\Front\Traits\InputRules;
use WeblaborMx\Front\Traits\WithWidth;
use Illuminate\Support\Str;

class Input
{
	use InputVisibility, InputSetters, InputRules, WithWidth;

	public $is_input = true;
	public $is_panel = false;
	public $form_before = '';
	public $form_after = '';
	public $data_classes = '';
	public $title;
	public $column;
	public $extra;
	public $source; 

	public function __construct($title = null, $column = null, $extra = null, $source = null)
	{
		$this->title = $title;
		$this->column = $column;
		$this->extra = $extra;
		$this->source = $source;
		$this->load();
	}

	public static function make($title = null, $column = null, $extra = null) 
	{
		if(is_null($column) && !is_null($title) && is_string($title)) {
			$column = $title;
			$column = Str::snake($column);
		}

		$source = session('source');
		return new static($title, $column, $extra, $source);	
	}

	public function load()
	{
		// Do nothing
	}

	public function getValue($object)
	{
		$column = $this->column;
		if(!is_string($column) && is_callable($column)) {
			$return = $column($object);
		} else {
			$return = $object->$column;	
		}
		$return = isset($return) && strlen($return)>0 ? $return : '--';
		return $return;
	}

	public function getValueProcessed($object)
	{
		$return = $this->getValue($object);
		if(Str::startsWith($return, 'http') && !isset($this->link)) {
			$this->link = $return;
			$this->link_target = '_blank';
		}
		$link = $this->link;
		if(isset($link)) {
			$add = isset($this->link_target) ? ' target="'.$this->link_target.'"' : '';
			$return = "<a href='{$link}'{$add}>{$return}</a>";
		}
		if(isset($this->display_using) && is_callable($this->display_using) && $return!='--') {
			$function = $this->display_using;
			$return = $function($return);
		}
		return $return;
	}

	public function form()
	{
		return;
	}

	public function formHtml()
	{
		$input = $this;
		$html = view('front::input-form', compact('input'))->render();
		return $this->form_before.$html.$this->form_after;
	}

	public function showHtml($object)
	{
		$input = $this;
		$html = view('front::input-show', compact('input', 'object'))->render();
		return $this->validateConditional($object) ? $html : null;
	}

	public function setColumn($column)
	{
		$this->column = $column;
		return $this;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

}
