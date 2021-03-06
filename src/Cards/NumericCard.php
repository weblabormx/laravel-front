<?php

namespace WeblaborMx\Front\Cards;

use Illuminate\Support\Facades\Cache;

class NumericCard extends Card
{
    public $view = 'front::cards.numeric';
    public $fields = ['icon', 'number', 'text', 'subtitle', 'porcentage'];

    /*
     * Editable functions
     */

    public function value()
    {
        return;
    }

    public function old()
    {
        return;
    }

    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    public function showNumber($number)
    {
        return $number;
    }

    public function cacheName()
    {
        $name = get_class($this);
        return 'Card:'.$name.':'.\Auth::user()->currentTeam->id;
    }

    /*
     * Functions
     */

    public function load()
    {
        
        $values = Cache::remember($this->cacheName(), $this->cacheFor(), function() {
            return [
                'number' => $this->value(),
                'porcentage' => $this->calculatePorcentage($this->value(), $this->old())
            ];
        });
        $this->number = $values['number'];
        $this->porcentage = $values['porcentage'];
    }

    public function calculatePorcentage($now, $before)
    {
        if($before==0) {
            return 0;
        }
        $diff = $now - $before;
        return round(($diff / $before) * 100, 0);
    }
}
