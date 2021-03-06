<?php

namespace WeblaborMx\Front\Actions;

use Illuminate\Support\Str;
use WeblaborMx\Front\Components\Panel;

class IndexAction
{
	public $title;
    public $icon = 'fa fa-book';
    public $show = true;
    public $show_button = true;
	public $data;
    public $save_button = 'Save changes';
    public $slug;

	public function __construct()
	{
		if(!isset($this->title)) {
    		$title = Str::snake(class_basename(get_class($this)));
	    	$title = ucwords(str_replace('_', ' ', $title));
	    	$this->title = $title;
    	}
        if(is_null($this->slug)) {
            $this->slug = Str::slug(Str::snake(class_basename(get_class($this))));
        }
		$this->button_text = "<i class='{$this->icon}'></i> $this->title";
	}

	public function addData($data)
	{
		$this->data = $data;
		return $this;
	}

	public function buttons()
    {
        return [];
    }

    public function fields()
    {
        return [];
    }

    public function validate()
    {
    	$rules = collect($this->fields())->filter(function($item) {
    		return strlen($item->getRules())>0;
    	})->mapWithKeys(function($item) {
            return [$item->column => $item->getRules()];
        })->toArray();
    	\Validator::make(request()->all(), $rules)->validate();
    }

    public function getFieldsWithPanel()
    {
        $fields = collect($this->fields());
        $components = $fields->filter(function($item) {
            return class_basename(get_class($item)) == 'Panel';
        })->filter(function($item) {
            return $item->fields()->count() > 0;
        });
        $fields = $fields->filter(function($item) {
            return class_basename(get_class($item)) != 'Panel';
        });
        if($fields->count() > 0) {
            $components[-1] = Panel::make('', $fields); 
        }
        return $components->sortKeys()->values();
    }

    public function show($function)
    {
    	$this->show = $function();
    	return $this;
    }

    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    public function getStyle()
    {
        if(isset($this->color)) {
            return 'background: '.$this->color;
        }
        return '';
    }

    public function __get($name)
    {
        if (isset($this->object) && isset($this->object->$name)) {
            return $this->object->$name;
        }
    }

    public function __isset($name)
    {
        if (isset($this->object) && isset($this->object->$name)) {
            return $this->object->$name;
        }
    }

}
