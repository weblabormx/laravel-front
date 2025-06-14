<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image as Intervention;
use Illuminate\Support\Str;
use WeblaborMx\Front\Facades\Front;

class Image extends Input
{
    public $directory = 'images';
    public $view_size = 'm';
    public $visibility = 'public';
    public $max_size = 5000;
    public $original_size;
    public $file_name;
    public $url_returned;
    public $extension;
    public $save = true;
    public $thumbnails = [];

    /*
     * Basic functions
     */

    public function load()
    {
        $this->url_returned = function ($file_name) {
            return Storage::url($file_name);
        };
        $this->thumbnails = config('front.thumbnails', []);
    }

    public function form()
    {
        $input = $this;
        $id = 'file_' . rand();
        return view('front::inputs.image-form', compact('input', 'id'));
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        if ($value == '--') {
            return $value;
        }
        $original = $value;
        $thumb = Front::thumbs()->get($value, $this->view_size);
        return view('front::inputs.image', compact('original', 'thumb'));
    }

    /*
     * Setters
     */

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
        $this->thumbnails = collect($this->thumbnails)->filter(function ($item) use ($thumbs) {
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

    public function sizeToShow($size)
    {
        $this->view_size = $size;
        return $this;
    }

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    public function setMaxSize($max_size)
    {
        $this->max_size = $max_size;
        return $this;
    }

    public function noSave()
    {
        $this->save = false;
        return $this;
    }

    /*
     * Processing
     */

    public function processData($data)
    {
        if (!isset($data[$this->column . '_new'])) {
            unset($data[$this->column]);
            return $data;
        }
        $file = $data[$this->column . '_new'];

        // Assign data to request
        $data[$this->column] = $file;
        unset($data[$this->column . '_new']);
        return $data;
    }

    public function processDataAfterValidation($data)
    {
        if (!isset($data[$this->column])) {
            return $data;
        }
        if (!$this->save) {
            unset($data['image']);
            return $data;
        }
        $file = $data[$this->column];

        // Remove old files
        if (isset($this->resource) && isset($this->resource->object)) {
            $object = $this->resource->object;
            $this->removeAction($object);
        }

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
        return $data;
    }

    public function validate($data)
    {
        $name = $this->column;
        $attribute_name = $this->title;

        $rules = [
            $name => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:' . $this->max_size]
        ];

        $attributes = [
            $name => $attribute_name
        ];

        Validator::make($data, $rules, [], $attributes)->validate();
    }

    public function removeAction($object)
    {
        // Get url of the image
        $column = $this->column;
        $value = $object->$column;

        // Get base url (The text before the url)
        $function = $this->url_returned;
        $format = $function('test.test');
        $base = str_replace('test.test', '', $format);

        // Get file names
        $original_file_name = str_replace($base, '', $value);
        $file_names = array();
        $file_names[] = $original_file_name;
        foreach ($this->thumbnails as $thumbnail) {
            $file_names[] = Front::thumbs()->get($original_file_name, $thumbnail['prefix']);
        }

        // Remove the files
        Storage::delete($file_names);
        return;
    }

    /*
     * Internal functions
     */

    public function saveNewSize($file, $file_name, $width, $height, $prefix, $is_fit = false)
    {
        // Make smaller the image
        $new_file = Intervention::make($file);

        if ($is_fit) {
            $new_file = $new_file->fit($width, $height);
        } elseif ($new_file->height() > $height || $new_file->width() > $width) {
            $new_file = $new_file->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        // Save the image
        $new_name = Front::thumbs()->get($file_name, $prefix, true);
        $file_name = $this->directory . '/' . $new_name;
        $storage_file = Storage::put($file_name, (string) $new_file->encode(), $this->visibility);
        if ($storage_file == false) {
            abort(406, "{$file_name} wasn't uploaded");
        }
        $url_returned = $this->url_returned;
        return $url_returned($file_name);
    }

    public function getFileName($data, $file)
    {
        $file_name = $this->file_name;
        if (is_callable($file_name)) {
            $file_name = $file_name($data);
        }
        if (is_null($file_name)) {
            $file_name = Str::random(9);
        }
        if (!is_null($file_name)) {
            $extension = $this->extension ?? $file->guessExtension();
            $file_name .= '.' . $extension;
        }
        return $file_name;
    }

    public function saveOriginalFile($data, $file)
    {
        // Get File Name
        $set_file_name = $this->getFileName($data, $file);

        // If original sizes were defined then save as thumb
        if (!is_null($this->original_size) && is_array($this->original_size)) {
            $url = $this->saveNewSize($file, $set_file_name, $this->original_size['width'], $this->original_size['height'], '', $this->original_size['fit']);
            return ['file_name' => $set_file_name, 'url' => $url];
        }

        // Save original file
        if (!is_null($set_file_name)) {
            $storage_file = Storage::putFileAs($this->directory, $file, $set_file_name, $this->visibility);
        } else {
            $storage_file = Storage::putFile($this->directory, $file, $this->visibility);
        }

        $file_name = class_basename($storage_file);
        $url_returned = $this->url_returned;
        $url = $url_returned($this->directory . '/' . $file_name);
        return compact('file_name', 'url');
    }
}
