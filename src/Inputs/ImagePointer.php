<?php

namespace WeblaborMx\Front\Inputs;

class ImagePointer extends Input
{
    public $image;
    public $width = '100%';

    public function form()
    {
        $rand = rand(1, 100);
        $id = 'image-to-get-coordiantes-' . $rand;
        $this->attributes['data-type'] = 'image-coordinate';
        $this->attributes['data-image'] = $id;
        $html = '<img src="' . $this->image . '" id="' . $id . '" width="' . $this->width . ';" />';

        $html .= html()
            ->hidden($this->column, $this->default_value)
            ->attributes($this->attributes);

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
}
