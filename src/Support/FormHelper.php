<?php

namespace WeblaborMx\Front\Support;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class FormHelper
{
    /**
     * Open a new form
     *
     * @param array $options
     * @return string
     */
    public static function open(array $options = [])
    {
        $method = isset($options['method']) ? strtoupper($options['method']) : 'POST';
        $url = isset($options['url']) ? $options['url'] : '';
        $files = isset($options['files']) && $options['files'] === true;
        $attributes = [];
        
        // Set form attributes
        $attributes['action'] = $url;
        $attributes['method'] = in_array($method, ['GET', 'POST']) ? $method : 'POST';
        $attributes['enctype'] = $files ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        
        // Add any additional attributes
        foreach ($options as $key => $value) {
            if (!in_array($key, ['method', 'url', 'files'])) {
                $attributes[$key] = $value;
            }
        }
        
        // Build attributes string
        $attributesStr = self::buildAttributesString($attributes);
        
        // Add CSRF token for non-GET requests
        $csrf = '';
        if ($method !== 'GET') {
            $csrf = '<input type="hidden" name="_token" value="' . Session::token() . '">';
            
            // Add method field for non-POST/GET requests
            if (!in_array($method, ['GET', 'POST'])) {
                $csrf .= '<input type="hidden" name="_method" value="' . $method . '">';
            }
        }
        
        return '<form ' . $attributesStr . '>' . $csrf;
    }
    
    /**
     * Close the form
     *
     * @return string
     */
    public static function close()
    {
        return '</form>';
    }
    
    /**
     * Create a form model binding
     *
     * @param mixed $model
     * @param array $options
     * @return string
     */
    public static function model($model, array $options = [])
    {
        return self::open($options);
    }
    
    /**
     * Create a text input field
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public static function text($name, $value = null, array $attributes = [])
    {
        return self::input('text', $name, $value, $attributes);
    }
    
    /**
     * Create a password input field
     *
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public static function password($name, array $attributes = [])
    {
        return self::input('password', $name, null, $attributes);
    }
    
    /**
     * Create a hidden input field
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public static function hidden($name, $value = null, array $attributes = [])
    {
        return self::input('hidden', $name, $value, $attributes);
    }
    
    /**
     * Create a number input field
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public static function number($name, $value = null, array $attributes = [])
    {
        return self::input('number', $name, $value, $attributes);
    }
    
    /**
     * Create a date input field
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public static function date($name, $value = null, array $attributes = [])
    {
        return self::input('date', $name, $value, $attributes);
    }
    
    /**
     * Create a datetime-local input field
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public static function datetimeLocal($name, $value = null, array $attributes = [])
    {
        return self::input('datetime-local', $name, $value, $attributes);
    }
    
    /**
     * Create a file input field
     *
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public static function file($name, array $attributes = [])
    {
        return self::input('file', $name, null, $attributes);
    }
    
    /**
     * Create a textarea field
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public static function textarea($name, $value = null, array $attributes = [])
    {
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        
        $attributesStr = self::buildAttributesString($attributes);
        
        return '<textarea ' . $attributesStr . '>' . htmlspecialchars($value ?? '') . '</textarea>';
    }
    
    /**
     * Create a select field
     *
     * @param string $name
     * @param array $options
     * @param mixed $selected
     * @param array $attributes
     * @return string
     */
    public static function select($name, array $options = [], $selected = null, array $attributes = [])
    {
        $attributes['name'] = $name;
        $attributes['id'] = $attributes['id'] ?? $name;
        
        $attributesStr = self::buildAttributesString($attributes);
        
        $html = '<select ' . $attributesStr . '>';
        
        foreach ($options as $value => $label) {
            $selectedAttr = self::isSelected($value, $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>' . htmlspecialchars($label) . '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    /**
     * Create a checkbox field
     *
     * @param string $name
     * @param mixed $value
     * @param bool $checked
     * @param array $attributes
     * @return string
     */
    public static function checkbox($name, $value = 1, $checked = null, array $attributes = [])
    {
        return self::checkable('checkbox', $name, $value, $checked, $attributes);
    }
    
    /**
     * Create a submit button
     *
     * @param string $value
     * @param array $attributes
     * @return string
     */
    public static function submit($value = null, array $attributes = [])
    {
        return self::input('submit', null, $value, $attributes);
    }
    
    /**
     * Get the value attribute for a form element
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function getValueAttribute($name, $value = null)
    {
        if ($value !== null) {
            return $value;
        }
        
        if (Request::old($name) !== null) {
            return Request::old($name);
        }
        
        return null;
    }
    
    /**
     * Create a generic input field
     *
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    protected static function input($type, $name, $value = null, array $attributes = [])
    {
        $attributes['type'] = $type;
        
        if ($name !== null) {
            $attributes['name'] = $name;
            $attributes['id'] = $attributes['id'] ?? $name;
        }
        
        if ($type !== 'password' && $value !== null) {
            $attributes['value'] = $value;
        }
        
        $attributesStr = self::buildAttributesString($attributes);
        
        return '<input ' . $attributesStr . '>';
    }
    
    /**
     * Create a checkable input field
     *
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param bool $checked
     * @param array $attributes
     * @return string
     */
    protected static function checkable($type, $name, $value, $checked, array $attributes = [])
    {
        if ($checked) {
            $attributes['checked'] = 'checked';
        }
        
        return self::input($type, $name, $value, $attributes);
    }
    
    /**
     * Determine if the value is selected
     *
     * @param string $value
     * @param mixed $selected
     * @return bool
     */
    protected static function isSelected($value, $selected)
    {
        if (is_array($selected)) {
            return in_array($value, $selected);
        }
        
        return (string) $value === (string) $selected;
    }
    
    /**
     * Build an HTML attribute string from an array
     *
     * @param array $attributes
     * @return string
     */
    protected static function buildAttributesString(array $attributes)
    {
        $html = [];
        
        foreach ($attributes as $key => $value) {
            // Boolean attributes
            if (is_bool($value) && $value) {
                $html[] = $key;
            } 
            // Regular attributes
            elseif (!is_bool($value)) {
                $html[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        
        return count($html) > 0 ? implode(' ', $html) : '';
    }
}
