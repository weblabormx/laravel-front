<?php

namespace WeblaborMx\Front\Jobs;

class MassiveIndexEditShow
{
    public $front;
    public $object;
    public $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front)
    {
        $this->front = $front;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Set session
        \Cache::store('array')->put('is_massive', true);

        // Get objects
        $result = $this->front->globalIndexQuery()->limit($this->front->pagination)->get();

        // Return generated data
        return compact('result');
    }
}
