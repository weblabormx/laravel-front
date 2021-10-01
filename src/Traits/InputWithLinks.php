<?php

namespace WeblaborMx\Front\Traits;

use WeblaborMx\Front\Front;
use WeblaborMx\Front\Texts\Button;
use Illuminate\Support\Str;

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
        $can_edit = !isset($this->block_edition) || !$this->block_edition;

        // Add actions links
        if(isset($this->actions) && count($this->actions)>0) {
            foreach($this->actions as $action) {
            	$links[] = Button::make($action->button_text)
            		->addLink("{$this->front->getBaseUrl()}/{$object->getKey()}/action/{$action->slug}");
            }
        }

        // Show links added manually
        foreach($this->links as $link => $text) {
        	$links[] = Button::make($text)->addLink($link);
        }

        // Add massive edit link
        if(isset($this->massive_edit_link) && $this->show_massive && $can_edit) {
        	$links[] = Button::make("<i class='fa fa-edit'></i> ".__('Edit')." {$this->front->plural_label}")
        		->addLink("{$front->getBaseUrl()}/{$object->getKey()}/massive_edit/{$key}{$this->massive_edit_link}");
        }
        
        // Add create link
        if(isset($this->create_link) && $this->front->canCreate() && $can_edit) {
            $title = Str::singular($this->title) ?? $this->front->label;
        	$links[] = Button::make("<span class='fa fa-plus'></span> ".__('Add')." {$title}")
        		->addLink($this->create_link);
        }

        return $links;
    }
}
