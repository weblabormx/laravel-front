<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Validator;

class Images extends Image
{
    public $original_name_column;

    /*
     * Basic functions
     */

    public function form()
    {
        $input = $this;
        $id = 'file_' . rand();
        return view('front::inputs.images-form', compact('input', 'id'));
    }

    public function getValue($object)
    {
        return;
    }

    /*
     * Processing
     */

    public function processData($data)
    {
        return $data;
    }

    public function processDataAfterValidation($data)
    {
        if (!isset($data[$this->column])) {
            return $data;
        }
        if (!$this->save) {
            return $data;
        }

        $all_data = [];
        unset($data['_token']);
        $files = $data[$this->column];
        foreach ($files as $file) {
            $data[$this->column] = $file;
            $all_data[] = $data;
        }

        return collect($all_data)->map(function ($data) {
            // Save original file
            $file = $data[$this->column];
            $result = $this->saveOriginalFile($data, $file);
            $url = $result['url'];
            $file_name = $result['file_name'];

            // New sizes
            foreach ($this->thumbnails as $thumbnail) {
                $this->saveNewSize($file, $file_name, $thumbnail['width'], $thumbnail['height'], $thumbnail['prefix'], $thumbnail['fit']);
            }

            // Assign data to request
            $data[$this->column] = $url;

            // Save original name in a column if set
            if (!is_null($this->original_name_column)) {
                $data[$this->original_name_column] = $file->getClientOriginalName();
            }
            return $data;
        })->all();
    }

    public function validate($data)
    {
        $name = $this->column;
        $attribute_name = $this->title;
        $rules = [
            $name . '.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:' . $this->max_size]
        ];
        $attributes = [
            $name . '.*' => $attribute_name
        ];

        Validator::make($data, $rules, [], $attributes)->validate();
    }

    public function setOriginalNameColumn($value)
    {
        $this->original_name_column = $value;
        return $this;
    }
}
