<?php

namespace WeblaborMx\Front\Inputs;

class InputGroup extends Input
{
    public function __construct($before = null, $input = null, $after = null, $none = null)
    {
        if (!is_array($before) && !is_null($before)) {
            $before = [$before];
        }
        if (!is_array($after) && !is_null($after)) {
            $after = [$after];
        }
        $this->before = $before;
        $this->input = $input;
        $this->after = $after;
    }

    public static function make($before = null, $input = null, $after = null)
    {
        return new static($before, $input, $after);
    }

    public function form()
    {
        $group = $this;
        return view('front::input-group', compact('group'))->render();
    }
}
