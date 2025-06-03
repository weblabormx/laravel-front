<?php

namespace WeblaborMx\Front\Inputs;

use Illuminate\Contracts\Support\Htmlable;
use WeblaborMx\Front\Traits\InputVisibility;
use WeblaborMx\Front\Traits\InputSetters;
use WeblaborMx\Front\Traits\InputRules;
use WeblaborMx\Front\Traits\WithWidth;
use Illuminate\Support\Str;

class Input implements Htmlable, \Stringable
{
    use InputVisibility, InputSetters, InputRules, WithWidth;

    public $is_input = true;
    public $is_panel = false;
    public $form_before = '';
    public $form_after = '';
    public $data_classes = '';
    public $title;
    public $set_title_executed = false;
    public $needs_to_be_on_panel = true;
    public $column;
    public $extra;
    public $source;
    public $value;
    public $size;
    public $input_formatted = true;
    public $attributes;
    public $format;

    public function __construct($title = null, $column = null, $extra = null, $source = null)
    {
        if (is_string($title)) {
            $this->title = __($title);
        } else {
            $this->title = $title;
        }
        $this->column = $column;
        $this->extra = $extra;
        $this->source = $source;
        $this->attributes = config('front.default_input_attributes');
        $this->load();
    }

    public static function make($title = null, $column = null, $extra = null)
    {
        if (is_null($column) && !is_null($title) && is_string($title)) {
            $column = class_basename($title);
            $column = Str::snake($column);
        }

        $source = session('source');
        return new static($title, $column, $extra, $source);
    }

    public function load()
    {
        // Do nothing
    }

    public function setValue($value)
    {
        if (!is_string($value) && is_callable($value)) {
            $value = $value();
        }
        $this->value = $value;
        $this->default_value = $value;
        return $this;
    }

    public function getValue($object)
    {
        if (isset($this->value)) {
            return $this->value;
        }
        if (!isset($object)) {
            return;
        }
        $return = '';
        $column = $this->column;
        if (!is_string($column) && is_callable($column)) {
            $return = $column($object);
        } elseif (Str::contains($column, '.')) {
            $return = collect(explode('.', $column))->reduce(function ($carry, $item) use ($object) {
                if (isset($carry[$item])) {
                    return $carry[$item];
                }
                if (is_object($carry) && isset($carry->$item)) {
                    return $carry?->$item;
                }
                return null;
            }, $object);
        } else {
            $return = $object?->$column;
        }

        try {
            $return = $this->castReturnValue($return);
        } catch (\TypeError $th) {
            throw new \TypeError("Column '$column' can't be casted to string to be shown on input", 0, $th);
        }

        $return = isset($return) && strlen($return) > 0 ? $return : '--';
        return $return;
    }

    public function getValueProcessed($object)
    {
        $return = $this->getValue($object);
        if ($return != '--' && isset($this->format)) {
            $format = $this->format;
            $return = $format($return);
        }

        if (Str::startsWith($return, 'http') && !isset($this->link)) {
            $this->link = $return;
            $this->link_target = '_blank';
        }
        $link = $this->link;
        if (isset($link) && $return != '--') {
            $add = isset($this->link_target) ? ' target="' . $this->link_target . '"' : '';
            $return = "<a href='{$link}'{$add}>{$return}</a>";
        }
        if (isset($this->display_using) && is_callable($this->display_using) && $return != '--') {
            $function = $this->display_using;
            $return = $function($return);
        }
        return $return;
    }

    public function getColumn()
    {
        $column = $this->column;
        if (!Str::contains($column, '.')) {
            return $column;
        }

        $explode = explode('.', $column);
        $column = $explode[0];
        $key = $explode[1];
        return "{$column}[{$key}]";
    }

    public function form()
    {
        return;
    }

    public function hideForm()
    {
        return Hidden::make($this->title, $this->column)->form();
    }

    public function formHtml()
    {
        if ($this->hide && (request()->filled($this->column))) {
            return $this->hideForm();
        }
        if (!$this->input_formatted) {
            return $this->form();
        }
        $input = $this;
        $html = view('front::input-form', compact('input'))->render();
        return $this->form_before . $html . $this->form_after;
    }

    public function showHtml($object)
    {
        $input = $this;
        $html = view('front::input-show', compact('input', 'object'))->render();
        return $this->validateConditional($object) ? $html : null;
    }

    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = __($title);
        $this->set_title_executed = true;
        return $this;
    }

    public function size($size = null)
    {
        if (isset($this->attributes['style']) || is_null($size)) {
            return $this;
        }
        $this->size = $size;
        $this->attributes['style'] = 'width: ' . $size . 'px';
        return $this;
    }

    public function massiveSize($size = null)
    {
        if (\Cache::store('array')->get('is_massive') !== true) {
            return $this;
        }
        return $this->size($size);
    }

    // In case there default attributes for the model
    public function setDefaultValueFromAttributes($model)
    {
        if ($this->source != 'create' || !is_null($this->default_value) || is_null($model)) {
            return $this;
        }
        $model = new $model();
        $attributes = $model->getAttributes();
        if (isset($attributes[$this->column])) {
            $this->default($attributes[$this->column]);
        }
        return $this;
    }

    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Allow to edit the data passed to create function of the object, returns the request gotten
     **/

    public function processData($data)
    {
        return $data;
    }

    public function processDataAfterValidation($data)
    {
        return $data;
    }

    public function processAfterSave($object, $request)
    {
        //
    }

    /**
     * Can add extra validation to inputs in case is needed
     **/

    public function validate($data)
    {
        return;
    }

    /**
     * Action that is executed bofore an object is removed
     **/

    public function removeAction($object)
    {
        return;
    }
    
    /* -----------
     * Internal
     ----------- */

    public function toHtml()
    {
        return $this->formHtml();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Ensures that the returned value is a string
     */
    protected function castReturnValue(mixed $return): ?string
    {
        // Prevents model `$casts` exceptions
		// PHP 7.1 compatible
		if (isset($return) && !is_string($return) && !is_numeric($return) && !is_bool($return)) {
			if (is_scalar($return)) {
				$return = strval($return);
			} else if (enum_exists($return::class)) {
				$return = $return->value ?? $return->name;
			} else if (is_array($return)) {
				$return = json_encode($return);
			} else if (gettype($return) === 'object') {
				if (method_exists($return, '__toString')) {
					$return = $return->__toString();
				} else if ($return instanceof \BackedEnum) {
					$return = $return->value;
				} else if ($return instanceof \JsonSerializable) {
					$return = json_encode($return);
				}
			}
		}

        return $return;
    }
}
