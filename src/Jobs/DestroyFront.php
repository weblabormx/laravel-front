<?php

namespace WeblaborMx\Front\Jobs;

class DestroyFront
{
    public $front;
    public $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $object)
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
        // Call the action to be done before is deleted
        $this->front->destroy($this->object);

        // Delete the object
        $this->object->delete();
        
        // Show success message
        flash(__(':name deleted successfully', ['name' => $this->front->label]))->success();

        // Return true
        return true;
    }
}