<?php

namespace WeblaborMx\Front\Jobs;

class FrontDestroy
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
        $continue = $this->front->destroy($this->object);
        if (is_bool($continue) && !$continue) {
            return false;
        }

        // Process inputs actions for when is removed
        $this->front->processRemoves($this->object);

        // Delete the object
        $this->object->delete();

        // Show success message
        flash(__(':name deleted successfully', ['name' => $this->front->label]))->success();

        // Return true
        return true;
    }
}
