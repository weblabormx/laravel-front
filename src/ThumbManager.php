<?php

namespace WeblaborMx\Front;

// TODO: Check to document better
class ThumbManager
{
    /** @var callable|null */
    private mixed $validateWithCallable = null;

    /** @var callable|null */
    private mixed $editWithCallable = null;


    public function get(string $fullName, string $prefix, bool $ignoreValidation = false)
    {
        if (isset($this->validateWithCallable) && !$ignoreValidation) {
            $execute = ($this->validateWithCallable)($fullName);

            if (!$execute) {
                return $this->editThumb($fullName);
            }
        }

        $fullName = explode('/', $fullName);
        $key = count($fullName) - 1;

        $name = explode('.', $fullName[$key]);
        $name[0] = $name[0] . $prefix;
        $name = implode('.', $name);

        $fullName[$key] = $name;
        $fullName = implode('/', $fullName);

        return $this->editThumb($fullName);
    }

    protected function editThumb(string $name): string
    {
        if ($this->editWithCallable) {
            return ($this->editWithCallable)($name);
        }

        return $name;
    }
}
