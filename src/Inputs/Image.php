<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Image extends Input
{
	public $directory = 'images';

	public function form()
	{
		$input = $this;
		$id = 'file_'.rand();
		return view('front::inputs.image-form', compact('input', 'id'));
	}

	public function setDirectory($directory)
	{
		$this->directory = $directory;
		return $this;
	}

	public function processData($data)
	{
		if(!isset($data[$this->column.'_new'])) {
			return $data;
		}
		$file = Storage::putFile($this->directory, $data[$this->column.'_new']);
		$url = Storage::url($file);
		$data[$this->column] = $url;
		unset($data[$this->column.'_new']);
		return $data;
	}

	public function getValue($object)
	{
		$value = parent::getValue($object);
		return view('front::inputs.image', compact('value'));
	}

	public function validate($data)
	{
		$name = $this->column.'_new';
		$attribute_name = $this->title;
		$rules = [
			$name => ['image','mimes:jpeg,png,jpg,gif,svg']
		];
		$attributes = [
			$name => $attribute_name
		];
		\Validator::make($data, $rules, [], $attributes)->validate();
	}
}
