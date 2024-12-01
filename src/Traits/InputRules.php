<?php

namespace WeblaborMx\Front\Traits;

trait InputRules
{
    private $rules;
    private $creation_rules;
    private $update_rules;

    public function rules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    public function creationRules($rules)
    {
        $this->creation_rules = $rules;
        return $this;
    }

    public function updateRules($rules)
    {
        $this->update_rules = $rules;
        return $this;
    }

    public function getRules($source = 'store')
    {
        $rules = $this->rules ?? [];
        $rules = is_string($rules) ? [$rules] : $rules;
        if ($source == 'update' && isset($this->update_rules)) {
            $extra_rules = $this->update_rules;
        } elseif ($source == 'store' && isset($this->creation_rules)) {
            $extra_rules = $this->creation_rules;
        }
        if (isset($extra_rules)) {
            $extra_rules = is_string($extra_rules) ? [$extra_rules] : $extra_rules;
            $rules = collect($rules)->merge($extra_rules)->toArray();
        }
        if (!$this->validateConditional(request())) {
            return [];
        }
        return $rules;
    }
}
