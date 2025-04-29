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
        $rules = $this->rulesAsArray();

        if ($source == 'update' && isset($this->update_rules)) {
            $extra_rules = $this->update_rules;
        } elseif ($source == 'store' && isset($this->creation_rules)) {
            $extra_rules = $this->creation_rules;
        }

        if (isset($extra_rules)) {
            $extra_rules = is_string($extra_rules) ? [$extra_rules] : $extra_rules;
            $rules = array_merge($rules, $extra_rules);
        }

        if (!$this->validateConditional(request())) {
            return [];
        }

        return $rules;
    }

    /* ----------
     * Helpers
     ------------ */

    public function required()
    {
        $this->rules = $this->rulesAsArray();
        $this->rules[] = 'required';
        return $this;
    }

    /** @internal */
    private function rulesAsArray(): array
    {
        $rules = $this->rules ?? [];
        $rules = is_string($rules) ? [$rules] : $rules;
        return $rules;
    }
}
