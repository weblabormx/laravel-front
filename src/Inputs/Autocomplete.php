<?php

namespace WeblaborMx\Front\Inputs;

class Autocomplete extends Input
{
    public $url;
    public $text;

    public function form()
    {
        $this->attributes['data-type'] = 'autocomplete';
        $this->attributes['src'] = $this->url;

        $value = $this->getDefaultValue() ?? html()->value($this->column);
        if (!is_null($value)) {
            $this->attributes['data-selected-value'] = $value;
            // Fill text
            if (isset($this->text)) {
                $this->attributes['data-selected-text'] = $this->text;
            } else {
                $value = html()->value($this->column . '_text');
                $this->attributes['data-selected-text'] = $value;
            }
        }
        if ($this->source == 'create' || $this->source == 'edit') {
            $this->attributes['data-text-input'] = 'false';
        }

        if (isset($this->attributes['disabled'])) {
            return html()
                ->text($this->getColumn() . '_hidden', $this->attributes['data-selected-text'] ?? false)
                ->attributes(['disabled' => 'disabled']);
        }

        return html()
            ->text($this->getColumn(), $this->getDefaultValue())
            ->attributes($this->attributes);
    }

    public function setUrl($url)
    {
        $this->url = url($url);
        return $this;
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }
}
