<?php

namespace WeblaborMx\Front\Inputs;

class ToastEditor extends Input
{
    public float $height = 300;
    public string $previewStyle = 'tab';
    public string $lang = 'wysiwyg';
    public bool $dontTrim = false;
    public bool $hideModeSwitch = false;
    public bool $darkMode = false;
    public bool $hideImage = false;
    public bool $hideLink = false;
    public bool $hideTable = false;

    public function form()
    {
        $attributes = collect([
            'data-no-trim' => $this->dontTrim,
            'data-hide-switch' => $this->hideModeSwitch,
            'data-dark' => $this->darkMode,
            'data-hide-image' => $this->hideImage,
            'data-hide-link' => $this->hideLink,
            'data-hide-table' => $this->hideTable,
        ])->filter(function($value) {
            return $value;
        })->merge([
            'data-type' => 'toast-editor',
            'data-height' => "{$this->height}px",
            'data-preview' => $this->previewStyle,
            'data-lang' => $this->lang,
        ]);

        return html()
            ->textarea($this->getColumn(), $this->getDefaultValue())
            ->attributes($attributes->all());
    }

    public function setHeight($height = 500)
    {
        $this->height = $height;
        return $this;
    }

    public function setPreviewStyle($style = 'tab')
    {
        $this->previewStyle = $style;
        return $this;
    }

    public function setLang(string $lang = 'markdown')
    {
        $this->lang = $lang;
        return $this;
    }

    public function dontTrim(bool $enable = true)
    {
        $this->dontTrim = $enable;
        return $this;
    }

    public function hideModeSwitch(bool $enable = true)
    {
        $this->hideModeSwitch = $enable;
        return $this;
    }

    public function darkMode(bool $enable = true)
    {
        $this->darkMode = $enable;
        return $this;
    }

    public function wysiwyg()
    {
        $this->setLang('wysiwyg');
        return $this;
    }

    public function markdown()
    {
        $this->setLang('markdown');
        return $this;
    }

    public function hideImage(bool $enable = true)
    {
        $this->hideImage = $enable;
        return $this;
    }

    public function hideLink(bool $enable = true)
    {
        $this->hideLink = $enable;
        return $this;
    }

    public function hideTable(bool $enable = true)
    {
        $this->hideTable = $enable;
        return $this;
    }
}
