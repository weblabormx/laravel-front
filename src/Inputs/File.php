<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Input
{
	public $directory = 'files';

	public function form()
	{
		return \Form::file($this->column, $this->default_value, $this->attributes);
	}

	public function setDirectory($directory)
	{
		$this->directory = $directory;
		return $this;
	}

	public function processData($data)
	{
		$name = Storage::putFile($this->directory, $data);
		return Storage::url($name);
	}

	public function getValue($object)
	{
		$value = parent::getValue($object);
		return view('front::inputs.file', compact('value'));
	}
}
