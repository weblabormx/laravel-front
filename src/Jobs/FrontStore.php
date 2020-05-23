<?php

namespace WeblaborMx\Front\Jobs;

class FrontStore
{
    public $request;
    public $front;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $front)
    {
        $this->request = $request;
        $this->front = $front;
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

        // Process data after validation
        $data = $this->front->processDataAfterValidation($data);

        // Create the object
        $model = $this->front->getModel();
        $object = $model::create($data);

        // Call the action to be done after is created
        $this->front->store($object, $this->request);

        // Show success message
        flash(__(':name created successfully', ['name' => $this->front->label]))->success();
        
        // Redirect if there was a redirect value on the form
        if($this->request->filled('redirect_url')) {
            return redirect($this->request->redirect_url);
        }

        // Return the created object
        return $object;
    }
}
