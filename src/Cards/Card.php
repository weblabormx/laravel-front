<?php

namespace WeblaborMx\Front\Cards;

use BadMethodCallException;
use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\WithWidth;

class Card
{
    use WithWidth;

    protected $functions;
    public $data = [];

    public function __construct()
    {
        if (!isset($this->fields)) {
            return;
        }
        $this->functions = collect($this->fields)->map(function ($item) {
            return [
                'get'.ucfirst($item),
                'set'.ucfirst($item),
            ];
        })->flatten();
        $this->load();
    }

    /*
     * Functions
     */

    public function view()
    {
        $card = $this;
        return view($this->view, compact('card'));
    }

    public function html()
    {
        return $this->view()->render();
    }

    public function load()
    {
        return;
    }

    /*
     * Magic Functions
     */

    public function getter($name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        if (isset($this->$name)) {
            return $this->$name;
        }
        return $this->data[$name] ?? null;
    }

    public function setter($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    public function __call($method, $arguments)
    {
        if (!isset($this->functions) || !$this->functions->contains($method)) {
            throw new BadMethodCallException('The method '.get_class($this).'::'.$method.' can\'t be chained');
        }
        if (Str::startsWith($method, 'get')) {
            $name = strtolower(str_replace('get', '', $method));
            return $this->getter($name);
        }
        if (Str::startsWith($method, 'set')) {
            $name = strtolower(str_replace('set', '', $method));
            return $this->setter($name, $arguments[0]);
        }
    }
}
