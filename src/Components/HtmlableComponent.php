<?php

namespace WeblaborMx\Front\Components;

use Illuminate\Contracts\Support\Htmlable;
use InvalidArgumentException;

class HtmlableComponent extends Component
{
    /** @var Htmlable */
    private $htmlable;

    /**
     * @param Htmlable $htmlable
     */
    public function __construct($htmlable)
    {
        if (!$htmlable instanceof Htmlable) {
            throw new InvalidArgumentException("Not Htmlable passed as argument");
        }

        $this->htmlable = $htmlable;

        parent::__construct();
    }

    public function form()
    {
        return $this->htmlable->toHtml();
    }
}
