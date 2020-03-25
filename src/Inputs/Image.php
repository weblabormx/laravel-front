<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as Intervention;

class Image extends Input
{
	public $directory = 'images';
	public $view_size = 'm';
	public $thumbnails = [
		['prefix' => 's', 'size' => 90,   'fit' => true],  // Small Square
		['prefix' => 'b', 'size' => 160,  'fit' => true],  // Big Square
		['prefix' => 't', 'size' => 160,  'fit' => false], // Small Thumbnail
		['prefix' => 'm', 'size' => 320,  'fit' => false], // Medium Thumbnail
		['prefix' => 'l', 'size' => 640,  'fit' => false], // Large Thumbnail
		['prefix' => 'h', 'size' => 1024, 'fit' => false], // Huge Thumbnail
	];

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

	public function addThumb($prefix, $size, $fit)
	{
		$this->thumbnails[] = ['prefix' => $prefix, 'size' => $size, 'fit' => $fit];
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
		foreach ($this->thumbnails as $thumbnail) {
			$this->saveNewSize($file, $file_name, $thumbnail['size'], $thumbnail['prefix'], $thumbnail['fit']); 
		}
		
		// Assign data to request
		$data[$this->column] = $url;
		unset($data[$this->column.'_new']);
		return $data;
	}

	public function getValue($object)
	{
		$name = $this->column;
		$value = getThumb($object->$name, $this->view_size);
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
		$new_name = getThumb($file_name, $prefix);
		Storage::put($this->directory.'/'.$new_name, $new_file->encode());
	}
}
