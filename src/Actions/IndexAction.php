<?php

namespace WeblaborMx\Front\Actions;

use Illuminate\Support\Str;
use WeblaborMx\Front\Components\Panel;
use WeblaborMx\Front\Traits\HasInputs;
use WeblaborMx\Front\Traits\IsValidated;

class IndexAction
{
    use HasInputs, IsValidated;
    
	public $title;
    public $icon = 'fa fa-book';
    public $show = true;
    public $show_button = true;
	public $data;
    public $save_button;
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
        if(is_null($this->save_button)) {
            $this->save_button = __('Save changes');
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
        $fields = collect($this->fields());
        $data = request()->all();
        $this->makeValidation($fields, $data);
        return $this;
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

    public function show($result)
    {
    	if(!is_string($result) && is_callable($result)) {
            $result = $result();
        } 
        $this->show = $result;
        return $this;
    }

    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    public function showButton($show = true)
    {
        $this->show_button = $show;
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
