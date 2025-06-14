<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Support\Facades\Storage;

class File extends Input
{
    public $directory = 'files';
    public $visibility = 'public';
    public $original_name_column;

    public function form()
    {
        $input = $this;
        return view('front::inputs.file-form', compact('input'));
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    public function processData($data)
    {
        if (!isset($data[$this->column])) {
            return $data;
        }
        $file = Storage::putFile($this->directory, $data[$this->column], $this->visibility);
        $url = Storage::url($file);

        // Save original name in a column if set
        if (!is_null($this->original_name_column)) {
            $data[$this->original_name_column] = $data[$this->column]->getClientOriginalName();
        }
        $data[$this->column] = $url;
        return $data;
    }

    public function getValue($object)
    {
        $value = parent::getValue($object);
        return view('front::inputs.file', compact('value'));
    }

    public function setOriginalNameColumn($value)
    {
        $this->original_name_column = $value;
        return $this;
    }

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }
}
