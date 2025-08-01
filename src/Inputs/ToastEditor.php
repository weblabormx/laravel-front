<?php

namespace WeblaborMx\Front\Inputs;

class ToastEditor extends Input
{
    public float $height = 300;
    public string $previewStyle = 'tab';
    public string $lang = 'markdown';
    public bool $dontTrim = false;
    public bool $hideModeSwitch = false;
    public bool $darkMode = false;

    public function form()
    {
        $bools = \array_filter([
            'data-no-trim' => $this->dontTrim,
            'data-hide-switch' => $this->hideModeSwitch,
            'data-dark' => $this->darkMode,
        ]);

        return \html()
            ->textarea($this->column, $this->default_value)
            ->attributes([
                'data-type' => 'toast-editor',
                'data-height' => "{$this->height}px",
                'data-preview' => $this->previewStyle,
                'data-lang' => $this->lang,
                ...$bools
            ]);
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
}
