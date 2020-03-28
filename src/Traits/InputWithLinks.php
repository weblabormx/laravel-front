<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Front;
use WeblaborMx\Front\Texts\Button;

trait InputWithLinks
{
	public $links = [];
	
	public function addLinks($links)
	{
		if(!$this->showOnHere()) {
			return $this;
		}
		if(!is_string($links) && is_callable($links)) {
			$links = $links();
		}
		$this->links = $links;
		return $this;
	}

	public function getLinks($object, $key, $front)
    {
        $links = [];

        // Add actions links
        if(isset($this->actions) && count($this->actions)>0) {
            foreach($this->actions as $action) {
            	$links[] = Button::make($action->button_text)
            		->addLink("{$this->front->base_url}/{$object->getKey()}/action/{$action->slug}");
            }
        }

        // Show links added manually
        foreach($this->links as $link => $text) {
        	$links[] = Button::make($text)->addLink($link);
        }

        // Add massive edit link
        if(isset($this->masive_edit_link) && $this->show_massive) {
        	$links[] = Button::make("<i class='fa fa-edit'></i> ".__('Edit')." {$this->front->plural_label}")
        		->addLink("{$front->base_url}/{$object->getKey()}/masive_edit/{$key}{$this->masive_edit_link}");
        }
        
        // Add create link
        if( \Auth::user()->can('create', $this->front->getModel()) && isset($this->create_link)) {
        	$links[] = Button::make("<span class='fa fa-plus'></span> ".__('Add')." {$this->front->label}")
        		->addLink($this->create_link);
        }

        return $links;
    }
}
