<?php

namespace WeblaborMx\Front\Workers;

use WeblaborMx\Front\Facades\Front;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Worker
{
    use AuthorizesRequests;

    public $is_input = false;
    public $front;

    public function __construct($front, $column = null, $extra = null, $source = null)
    {
        $this->source = $source;
        $this->front = Front::makeResource($front, $this->source);
    }

    public static function make($title = null, $column = null, $extra = null)
    {
        $source = session('source');
        return new static($title, $column, $extra, $source);
    }

    public function handle()
    {
        //
    }

    public function execute()
    {
        try {
            return $this->handle();
        } catch (ValidationException $e) {
            return collect($e->errors())->flatten(1)->implode('<br />');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
