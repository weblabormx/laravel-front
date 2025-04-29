<?php

namespace WeblaborMx\Front\Texts;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class Button extends Text
{
    public $link  = '#';
    public $style = '';
    public $extra = '';
    public $class = '';
    public $type = 'btn-primary';
    public $icon = '';

    public function load()
    {
        $this->generateText();
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

	public function setIcon($icon)
	{
		if (!Str::contains($icon, '<') && Str::contains($icon, 'fa')) {
			$icon = "<i class='$icon'></i>";
		} else if (!Str::contains($icon, '<')) {
			$data = [
				'name' => $icon, // Reemplaza con el nombre del icono
				'class' => 'w-5 h-5',
			];
			$icon = Blade::render('<x-icon :name="$name" :class="$class" />', $data);
		}
		$this->icon = $icon;
		$this->generateText();
		return $this;
	}

    public function setTitle($title)
    {
        parent::setTitle($title);
        $this->generateText();
        return $this;
    }

    private function generateText()
    {
        $this->text = $this->icon.$this->title;
    }
}
