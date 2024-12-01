<?php

namespace WeblaborMx\Front\Inputs;

class ImageCropper extends Input
{
    public $image;
    public $handler;
    public $ratio = null;
    public $width = 500;
    public $min_sizes = null;
    public $max_sizes = null;

    public function form()
    {
        $rand = rand(1, 100);
        $id = 'image-to-crop-' . $rand;
        $function = "handler{$rand}";

        $this->attributes['data-type'] = 'cropper';
        $this->attributes['data-image'] = $id;
        $this->attributes['data-width'] = $this->width;
        $this->attributes['data-ratio'] = $this->ratio;

        if (isset($this->handler)) {
            $this->attributes['data-handler'] = $function;
        }

        if (isset($this->max_sizes)) {
            $this->attributes['data-max-sizes'] = $this->max_sizes[0] . ',' . $this->max_sizes[1];
        }

        if (isset($this->min_sizes)) {
            $this->attributes['data-min-sizes'] = $this->min_sizes[0] . ',' . $this->min_sizes[1];
        }

        $html = '<img src="' . $this->image . '" id="' . $id . '" style="max-width: none !important;" />';

        $html .= html()
            ->hidden($this->column, $this->default_value)
            ->attributes($this->attributes);

        if (isset($this->handler)) {
            $handler = $this->handler;
            $html .= '<script>function ' . $function . '(c) { ' . $handler() . ' }</script>';
        }

        return $html;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
        return $this;
    }

    public function setMaxSizes($width, $height)
    {
        $this->max_sizes = [$width, $height];
        return $this;
    }

    public function setMinSizes($width, $height)
    {
        $this->min_sizes = [$width, $height];
        return $this;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }
}
