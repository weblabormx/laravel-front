<?php

namespace WeblaborMx\Front\Components;

use WeblaborMx\Front\Front;
use WeblaborMx\Front\Traits\InputWithActions;
use WeblaborMx\Front\Traits\InputWithLinks;

class Panel extends Component
{
	use InputWithActions, InputWithLinks;
	
	public $is_panel = true;
	
	public function formHtml()
	{
		$panel = $this;
		return view('front::components.panel-form', compact('panel'));
	}

	public function showHtml($object)
	{
		$panel = $this;
		$is_input = $this->fields()->first()->is_input;
		return view('front::components.panel', compact('panel', 'object', 'is_input'));
	}

	public function html()
	{
		$input = $this;
		$value = $this->showHtml(null);
		return view('front::input-outer', compact('value', 'input'))->render();
	}

	public function getValue($object)
	{
		return $this->fields()->map(function($item) use ($object) {
			return $item->showHtml($object);
		})->implode('');
	}

	public function form()
	{
		return $this->fields()->map(function($item) {
			return $item->formHtml();
		})->implode('');
	}

	private function filterFields($where)
	{
		return collect($this->column)->filter(function($item) {
			return isset($item);
		})->flatten()->filter(function($item) use ($where) {
			$field = 'show_on_'.$where;
			return $item->$field;
		});
	}

	public function fields()
	{
		return $this->filterFields($this->source);
	}
}
