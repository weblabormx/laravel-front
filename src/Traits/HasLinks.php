<?php

namespace WeblaborMx\Front\Traits;

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

    public function getLinks($object=null)
    {
        if(is_null($object)) {
            return $this->getPageLinks();
        }

        $links = [];

        // Show index actions
        foreach($this->getActions() as $action) {
            $links[] = Button::make($action->button_text)->addLink($this->getBaseUrl()."/{$object->getKey()}/action/{$action->slug}");
        }

        // Show links added manually
        foreach($this->links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

         // Add delete button
        if($this->canRemove($object)) {
            $links[] = Button::make('<i class="fa fa-times pr-2"></i> '.__('Delete'))
                ->setExtra("data-type='confirm' title='".__('Delete')."' data-info='".__('Do you really want to remove this item?')."' data-button-yes='".__('Yes')."' data-button-no='".__('No')."' data-action='".url($this->getBaseUrl().'/'.$object->getKey())."' data-redirection='".url($this->getBaseUrl())."' data-variables='{ \"_method\": \"delete\", \"_token\": \"".csrf_token()."\" }'")
                ->setType('btn-danger');
        }

        // Add update button
        if($this->canUpdate($object)) {
            $extraUrl = str_replace(request()->url(), '', request()->fullUrl());
            $links[] = Button::make('<span class="fa fa-edit"></span> '. __('Edit'))->addLink("{$this->getBaseUrl()}/{$object->getKey()}/edit{$extraUrl}");
        }

        return $links;
    }

    public function getIndexLinks()
    {
    	$links = [];

    	// Show index actions
	    foreach($this->getIndexActions() as $action) {
            $query = request()->fullUrl();
            $query = explode('?', $query)[1] ?? '';
            $query = strlen($query) > 0 ? '?'.$query : '';
            $links[] = Button::make($action->button_text)->addLink($this->getBaseUrl()."/action/{$action->slug}{$query}");
	    }

        // Show links added manually
        foreach($this->index_links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        // Show massive edition
        if($this->enable_massive_edition) {
            $query = str_replace(url()->current(), '', url()->full());
            $url = $this->getBaseUrl()."/massive_edit".$query;
            $text = '<span class="fa fa-edit"></span> '. __('Edit');
            $links[] = Button::make($text)->addLink($url);
        }

        // Show create button
        if($this->show_create_button_on_index && $this->canCreate()) {
            $url = $this->create_link;
            $url = str_replace('{base_url}', $this->getBaseUrl(), $url);
            $links[] = Button::make('<span class="fa fa-plus"></span> '. __('Create') .' '.$this->label)->addLink($url);
        }
	    return $links;
    }

    public function getLenses()
    {
        $links = collect([]);

        // Show links to lenses
        if($this->is_a_lense && isset($this->normal_front)) {
            $icon = isset($this->normal_front->icon) ? '<i class="'.$this->normal_front->icon.'"></i> ': '';
            $title = $this->normal_front->lense_title ?? __('Normal View');
            $text = $icon.$title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl());
        } else {
            $icon = isset($this->icon) ? '<i class="'.$this->icon.'"></i> ': '';
            $title = $this->lense_title ?? __('Normal View');
            $text = $icon.$title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl())->setClass('active');
        }
        foreach($this->lenses() as $lense) {
            $class = '';
            if($this->is_a_lense && $lense->getLenseSlug()==$this->getLenseSlug()) {
                $class = 'active';
            }
            $icon = isset($lense->icon) ? '<i class="'.$lense->icon.'"></i> ': '';
            $title = $lense->lense_title;
            $text = $icon.$title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl()."/lenses/{$lense->getLenseSlug()}")->setClass($class);
        }

        return $links;
    }

    public function getPageLinks()
    {
        $links = [];

        // Show links added manually
        foreach($this->links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        return $links;
    }
}
