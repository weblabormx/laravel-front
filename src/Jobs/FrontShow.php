<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Support\Str;

class FrontShow
{
    public $front;
    public $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($object, $front)
    {
        $this->front = $front;
        $this->object = $object;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $object = $this->object;

        // Executing when showing an item
        $this->front->show($object);

        // Modify with indexResult
        $result = collect([$object]);
        $result = $this->front->indexResult($result);
        $object = $result->first();

        return $object;
    }
}
