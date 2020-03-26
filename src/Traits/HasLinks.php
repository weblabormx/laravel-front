<?php

namespace WeblaborMx\Front\Traits;

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

    public function all_index_links()
    {
    	$links = [];

    	// Show create button
    	if($this->show_create_button_on_index && \Auth::user()->can('create', $this->getModel())) {
            $links[$this->base_url.'/create'] = '<span class="fa fa-plus"></span> '. __('Create') .' '.$this->label;
    	}

    	// Show index actions
	    foreach($this->index_actions() as $action) {
	        $links[$this->base_url."/action/{$action->slug}"] = $action->button_text;
	    }

	    // Show links added manually
	    $links = collect($links)->merge($this->index_links());
	    return $links;
    }
}
