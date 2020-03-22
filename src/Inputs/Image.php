<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as Intervention;

class Image extends Input
{
	public $directory = 'images';
	public $view_size = '';

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
		$file = $data[$this->column.'_new'];

		// Save original file
		$storage_file = Storage::putFile($this->directory, $file);
		$file_name = class_basename($storage_file);
		$url = Storage::url($storage_file);

		// New sizes 
		$this->saveNewSize($file, $file_name, 90, 's', true); // Small Square
		$this->saveNewSize($file, $file_name, 160, 'b', true); // Big Square
		$this->saveNewSize($file, $file_name, 160, 't'); // Small Thumbnail
		$this->saveNewSize($file, $file_name, 320, 'm'); // Medium Thumbnail
		$this->saveNewSize($file, $file_name, 640, 'l'); // Large Thumbnail
		$this->saveNewSize($file, $file_name, 1024, 'h'); // Huge Thumbnail
		
		// Assign data to request
		$data[$this->column] = $url;
		unset($data[$this->column.'_new']);
		return $data;
	}

	public function getValue($object)
	{
		$name = $this->column;
		$value = $this->changeFileName($object->$name, $this->view_size);
		return view('front::inputs.image', compact('value'));
	}

	public function sizeToShow($size)
	{
		$this->view_size = $size;
		return $this;
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

	public function changeFileName($full_name, $prefix)
	{
		return getThumb($full_name, $prefix)
	}

	public function saveNewSize($file, $file_name, $size, $prefix, $is_fit = false)
	{
		$new_file = Intervention::make($file);
		if($is_fit) {
			$new_file = $new_file->fit($size, $size);	
		} else {
			$new_file = $new_file->resize($size, $size, function($constraint) {
			    $constraint->aspectRatio();
			});
		}
		$new_name = $this->changeFileName($file_name, $prefix);
		Storage::put($this->directory.'/'.$new_name, $new_file->encode());
	}
}
