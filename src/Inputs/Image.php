<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as Intervention;

class Image extends Input
{
	public $directory = 'images';
	public $view_size = 'm';
	public $original_size;
	public $file_name;
	public $url_returned;
	public $thumbnails = [
		['prefix' => 's', 'width' => 90,   'height' => 90,   'fit' => true],  // Small Square
		['prefix' => 'b', 'width' => 160,  'height' => 160,  'fit' => true],  // Big Square
		['prefix' => 't', 'width' => 160,  'height' => 160,  'fit' => false], // Small Thumbnail
		['prefix' => 'm', 'width' => 320,  'height' => 320,  'fit' => false], // Medium Thumbnail
		['prefix' => 'l', 'width' => 640,  'height' => 640,  'fit' => false], // Large Thumbnail
		['prefix' => 'h', 'width' => 1024, 'height' => 1024, 'fit' => false], // Huge Thumbnail
	];

	public function load()
	{
		$this->url_returned = function($file_name) {
			return Storage::url($file_name);
		};
	}

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

	public function setFileName($file_name)
	{
		$this->file_name = $file_name;
		return $this;
	}

	public function addThumb($prefix, $width, $height, $fit)
	{
		$this->thumbnails[] = compact('prefix', 'width', 'height', 'fit');
		return $this;
	}

	public function useThumbs(array $thumbs)
	{
		$this->thumbnails = collect($this->thumbnails)->filter(function($item) use ($thumbs) {
			return in_array($item['prefix'], $thumbs);
		})->values()->toArray();
		return $this;
	}

	public function setThumbs($thumbnails)
	{
		$this->thumbnails = $thumbnails;
		return $this;
	}

	public function setUrlReturned($url_returned)
	{
		$this->url_returned = $url_returned;
		return $this;
	}

	public function withoutThumbs()
	{
		$this->setThumbs([]); // Dont have thumbs
		$this->sizeToShow(''); // Show original file by default
		return $this;
	}

	public function originalSize($width, $height, $fit = false)
	{
		$this->original_size = compact('width', 'height', 'fit');
		return $this;
	}

	public function processData($data)
	{
		if(!isset($data[$this->column.'_new'])) {
			return $data;
		}
		$file = $data[$this->column.'_new'];

		// Save original file
		$result = $this->saveOriginalFile($data, $file);
		$url = $result['url'];
		$file_name = $result['file_name'];

		// New sizes
		foreach ($this->thumbnails as $thumbnail) {
			$this->saveNewSize($file, $file_name, $thumbnail['width'], $thumbnail['height'], $thumbnail['prefix'], $thumbnail['fit']); 
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

	public function saveNewSize($file, $file_name, $width, $height, $prefix, $is_fit = false)
	{
		$new_file = Intervention::make($file);
		if($is_fit) {
			$new_file = $new_file->fit($width, $height);	
		} else if ($new_file->height() > $height || $new_file->width() > $width) {
			$new_file = $new_file->resize($width, $height, function($constraint) {
			    $constraint->aspectRatio();
			});
		}

		$new_name = getThumb($file_name, $prefix);
		$file_name = $this->directory.'/'.$new_name;
		$storage_file = Storage::put($file_name, (string) $new_file->encode());
		$url_returned = $this->url_returned;
		return $url_returned($file_name);
	}

	private function getFileName($data, $file)
	{
		$file_name = $this->file_name;
		if(is_callable($file_name)) {
			$file_name = $file_name($data);
		}
		if(!is_null($file_name)) {
			$file_name .= '.'.$file->guessExtension();
		}
		return $file_name;
	}

	private function saveOriginalFile($data, $file)
	{
		// Get File Name
		$set_file_name = $this->getFileName($data, $file);

		// If original sizes were defined then save as thumb
		if(!is_null($this->original_size) && is_array($this->original_size)) {
			$url = $this->saveNewSize($file, $set_file_name, $this->original_size['width'], $this->original_size['height'], '', $this->original_size['fit']);
			return ['file_name' => $set_file_name, 'url' => $url];
		}

		// Save original file
		if(!is_null($set_file_name)) {
			$storage_file = Storage::putFileAs($this->directory, $file, $set_file_name);
		} else {
			$storage_file = Storage::putFile($this->directory, $file);
		}
		
		$file_name = class_basename($storage_file);
		$url_returned = $this->url_returned;
		$url = $url_returned($this->directory.'/'.$file_name);
		return compact('file_name', 'url');
	}
}
