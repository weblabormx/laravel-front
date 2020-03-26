<?php

namespace WeblaborMx\Front\Jobs;

class MassiveEditShow
{
    public $front;
    public $object;
    public $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $object, $key)
    {
        $this->front = $front;
        $this->object = $object;
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if relationship exists
        if(!isset($this->front->showRelations()[$this->key])) {
            abort(406, 'Key isnt correct');
        }

        // Get relationship input
        $input = $this->front->showRelations()[$this->key];
        $input_front = $input->front->addData($this->front->data);

        // Get relationship data
        $objects = $input->getResults($this->object);
        if(get_class($objects) != 'Illuminate\Support\Collection') {
            $objects = $objects->get();
        }

        // Return generated data
        return compact('input', 'input_front', 'objects');
    }
}