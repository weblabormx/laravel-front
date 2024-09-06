<?php

namespace WeblaborMx\Front;

class Source
{
    public const ACTION_INDEX = 'index';
    public const ACTION_CREATE = 'create';
    public const ACTION_STORE = 'store';
    public const ACTION_SHOW = 'show';
    public const ACTION_EDIT = 'edit';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DESTROY = 'destroy';

    /** @var string */
    private $value;

    /**  @param string $source  */
    public function __construct($source)
    {
        if (!in_array($source, $cases = static::cases())) {
            throw new \InvalidArgumentException("Front source must be one of the following: " . implode(',', $cases));
        }

        $this->value = $source;
    }


    /**  @param string|static $source  */
    public static function make($source)
    {
        if ($source instanceof static) {
            return $source;
        }

        return new static($source);
    }

    public static function cases()
    {
        return [
            static::ACTION_INDEX,
            static::ACTION_CREATE,
            static::ACTION_STORE,
            static::ACTION_SHOW,
            static::ACTION_EDIT,
            static::ACTION_UPDATE,
            static::ACTION_DESTROY,
        ];
    }

    public function isIndex()
    {
        return $this == static::ACTION_INDEX;
    }

    public function isCreate()
    {
        return $this == static::ACTION_CREATE;
    }

    public function isDestroy()
    {
        return $this == static::ACTION_DESTROY;
    }

    public function isEdit()
    {
        return $this == static::ACTION_EDIT;
    }

    public function isShow()
    {
        return $this == static::ACTION_SHOW;
    }

    public function isStore()
    {
        return $this == static::ACTION_STORE;
    }

    public function isUpdate()
    {
        return $this == static::ACTION_UPDATE;
    }

    public function isForm()
    {
        return $this != static::ACTION_INDEX && $this != static::ACTION_SHOW;
    }

    public function isServerSide()
    {
        return $this->isStore() ||
            $this->isUpdate() ||
            $this->isDestroy();
    }

    public function isClientSide()
    {
        return !$this->isServerSide();
    }

    public function __toString()
    {
        return $this->value;
    }
}
