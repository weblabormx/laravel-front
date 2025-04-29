<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Texts\Button;

trait HasLinks
{
    public $create_link = '{base_url}/create';

    public function index_links()
    {
        return [];
    }

    public function links()
    {
        return [];
    }

    public function getLinks($object = null)
    {
        if (is_null($object)) {
            return $this->getPageLinks();
        }

        $links = [];

        // Show index actions
        $actions = $this->getActions()->filter(function ($item) use ($object) {
            return $item->hasPermissions($object);
        });
        foreach ($actions as $action) {
            $links[] = Button::make($action->button_text)->addLink($this->getBaseUrl() . "/{$object->getKey()}/action/{$action->slug}");
        }

        // Show links added manually
        foreach ($this->links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        // Add delete button
        if ($this->canRemove($object)) {
            $links[] = Front::buttons()->getByName('delete', $this, $object);
        }

        // Add update button
        if ($this->canUpdate($object)) {
            $extraUrl = str_replace(request()->url(), '', request()->fullUrl());
            $url = "{$this->getBaseUrl()}/{$object->getKey()}/edit{$extraUrl}";
            $links[] = Front::buttons()->getByName('edit')->addLink($url);
        }

        return $links;
    }

    public function getIndexLinks()
    {
        $links = [];

        // Show index actions
        foreach ($this->getIndexActions() as $action) {
            $query = request()->fullUrl();
            $query = explode('?', $query)[1] ?? '';
            $query = strlen($query) > 0 ? '?' . $query : '';
            $links[] = Button::make($action->button_text)->addLink($this->getBaseUrl() . "/action/{$action->slug}{$query}");
        }

        // Show links added manually
        foreach ($this->index_links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        // Show massive edition
        if ($this->enable_massive_edition) {
            $query = str_replace(url()->current(), '', url()->full());
            $url = $this->getBaseUrl() . "/massive_edit" . $query;
            $links[] = Front::buttons()->getByName('edit')->addLink($url);
        }

        // Show create button
        if ($this->show_create_button_on_index && $this->canCreate()) {
            $url = $this->create_link;
            $url = str_replace('{base_url}', $this->getBaseUrl(), $url);
            $links[] = Front::buttons()->getByName('create')->setTitle(__('Create') . ' ' . $this->label)->addLink($url);
        }
        return $links;
    }

    public function getLenses()
    {
        $links = collect([]);

        // Show links to lenses
        if ($this->is_a_lense && isset($this->normal_front)) {
            $icon = isset($this->normal_front->icon) ? '<i class="' . $this->normal_front->icon . '"></i> ' : '';
            $title = $this->normal_front->lense_title ?? __('Normal View');
            $text = $icon . $title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl());
        } else {
            $icon = isset($this->icon) ? '<i class="' . $this->icon . '"></i> ' : '';
            $title = $this->lense_title ?? __('Normal View');
            $text = $icon . $title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl())->setClass('active');
        }
        foreach ($this->lenses() as $lense) {
            $class = '';
            if ($this->is_a_lense && $lense->getLenseSlug() == $this->getLenseSlug()) {
                $class = 'active';
            }
            $icon = isset($lense->icon) ? '<i class="' . $lense->icon . '"></i> ' : '';
            $title = $lense->lense_title;
            $text = $icon . $title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl() . "/lenses/{$lense->getLenseSlug()}")->setClass($class);
        }

        return $links;
    }

    public function getPageLinks()
    {
        $links = [];

        // Show links added manually
        foreach ($this->links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        return $links;
    }
}
