<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Texts\Button;
use Illuminate\Support\Facades\Gate;

trait HasLinks
{
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
        if( Gate::allows('delete', $object) && in_array('destroy', $this->actions) ) {
            $links[] = Button::make('<i class="fa fa-times pr-2"></i> '.__('Delete'))
                ->setExtra("data-type='confirm' title='".__('Delete')."' data-info='".__('Do you really want to remove this item?')."' data-button-yes='".__('Yes')."' data-button-no='".__('No')."' data-action='".url($this->getBaseUrl().'/'.$object->getKey())."' data-redirection='".url($this->getBaseUrl())."' data-variables='{ \"_method\": \"delete\", \"_token\": \"".csrf_token()."\" }'")
                ->setType('btn-danger');
        }

        // Add update button
        if( Gate::allows('update', $object) && in_array('edit', $this->actions) ) {
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
            $links[] = Button::make($action->button_text)->addLink($this->getBaseUrl()."/action/{$action->slug}");
	    }

        // Show links to lenses
        if($this->is_a_lense && isset($this->normal_front)) {
            $icon = isset($this->normal_front->icon) ? '<i class="'.$this->normal_front->icon.'"></i> ': '';
            $title = $this->normal_front->lense_title ?? __('Normal View');
            $text = $icon.$title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl());
        }
        foreach($this->lenses() as $lense) {
            if($this->is_a_lense && $lense->getLenseSlug()==$this->getLenseSlug()) {
                continue;
            }
            $icon = isset($lense->icon) ? '<i class="'.$lense->icon.'"></i> ': '';
            $title = $lense->lense_title;
            $text = $icon.$title;
            $links[] = Button::make($text)->addLink($this->getBaseUrl()."/lenses/{$lense->getLenseSlug()}");
        }

        // Show links added manually
        foreach($this->index_links() as $link => $text) {
            $links[] = Button::make($text)->addLink($link);
        }

        // Show create button
        if($this->show_create_button_on_index && Gate::allows('create', $this->getModel()) && in_array('create', $this->actions)) {
            $links[] = Button::make('<span class="fa fa-plus"></span> '. __('Create') .' '.$this->label)->addLink($this->getBaseUrl().'/create');
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
