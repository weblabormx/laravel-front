<?php

namespace WeblaborMx\Front\Jobs;

class FrontUpdate
{
    public $request;
    public $front;
    public $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $front, $object)
    {
        $this->request = $request;
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
        // Get data to be saved
        $data = $this->front->processData($this->request->all());

        // Validate
        $this->front->validate($data);
        
        // Update the object
        $this->object->update($data);

        // Call the action to be done after is updated
        $this->front->update($this->object, $this->request);
        
        // Show success message
        flash(__(':name updated successfully', ['name' => $this->front->label]))->success();

        // Redirect if there was a redirect value on the form
        if($this->request->filled('redirect_url')) {
            return redirect($this->request->redirect_url);
        }

        // Return the created object
        return $this->object;
    }
}