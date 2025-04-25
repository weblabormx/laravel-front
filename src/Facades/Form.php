<?php

namespace WeblaborMx\Front\Facades;

use Illuminate\Support\Facades\Facade;
use WeblaborMx\Front\Support\FormHelper;

/**
 * @method static string open(array $options = [])
 * @method static string close()
 * @method static string model($model, array $options = [])
 * @method static string text(string $name, mixed $value = null, array $attributes = [])
 * @method static string password(string $name, array $attributes = [])
 * @method static string hidden(string $name, mixed $value = null, array $attributes = [])
 * @method static string number(string $name, mixed $value = null, array $attributes = [])
 * @method static string date(string $name, mixed $value = null, array $attributes = [])
 * @method static string datetimeLocal(string $name, mixed $value = null, array $attributes = [])
 * @method static string file(string $name, array $attributes = [])
 * @method static string textarea(string $name, mixed $value = null, array $attributes = [])
 * @method static string select(string $name, array $options = [], mixed $selected = null, array $attributes = [])
 * @method static string checkbox(string $name, mixed $value = 1, bool $checked = null, array $attributes = [])
 * @method static string submit(string $value = null, array $attributes = [])
 * @method static mixed getValueAttribute(string $name, mixed $value = null)
 */
class Form extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'form';
    }
}
