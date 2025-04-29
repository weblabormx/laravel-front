<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Texts\Button;
use Illuminate\Support\Str;
use WeblaborMx\Front\Facades\Front;

trait InputWithLinks
{
    public $links = [];
    public $create_button_title;

    public function setCreateButtonTitle($title)
    {
        $this->create_button_title = $title;
        return $this;
    }

    public function addLinks($links)
    {
        if (!$this->showOnHere()) {
            return $this;
        }
        if (!is_string($links) && is_callable($links)) {
            $links = $links();
        }
        $this->links = $links;
        return $this;
    }

    public function getLinks($object, $key, $front)
    {
        $links = [];
        $can_edit = !isset($this->block_edition) || !$this->block_edition;

        // Add actions links
        if (isset($this->actions) && count($this->actions) > 0) {
            foreach ($this->actions as $action) {
                $url = "{$this->front->getBaseUrl()}/{$object->getKey()}/action/{$action->slug}";
                $links[] = Button::make($action->button_text)->addLink($url);
            }
        }

        // Show links added manually
        foreach ($this->links as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        // Add massive edit link
        if (isset($this->massive_edit_link) && $this->show_massive && $can_edit) {
            $extra_query = http_build_query(request()->all());
            if (strlen($extra_query) > 0) {
                $extra_query = '&' . $extra_query;
            }
            $url = "{$front->getBaseUrl()}/{$object->getKey()}/massive_edit/{$key}{$this->massive_edit_link}{$extra_query}";
            $links[] = Front::buttons()->getByName('edit')->addLink($url)->setTitle(__('Edit') . " {$this->front->plural_label}");
        }

        // Add create link
        if (isset($this->create_link) && strlen($this->create_link) > 0 && $this->front->canCreate() && $can_edit) {
            $title = Str::singular($this->title) ?? $this->front->label;
            $title = $this->create_button_title ?? __('Add') . " {$title}";
            $links[] = Front::buttons()->getByName('create')->addLink($this->create_link)->setTitle($title);
        }

        return $links;
    }
}
