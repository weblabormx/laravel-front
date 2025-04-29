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
        if (isResponse($data)) {
            return $data;
        }

        // Validate
        $this->front->validate($data);

        // Process data after validation
        $data = $this->front->processDataAfterValidation($data);

        // Process data Before saving
        $data = $this->front->processDataBeforeSaving($data);

        // Call the action to be done before is updated
        $this->front->beforeUpdate($this->object, $this->request);

        // Update the object
        $this->object->update($data);

        // Process actions after save
        $this->front->processAfterSave($this->object, $this->request);

        // Call the action to be done after is updated
        $this->front->update($this->object, $this->request);

        // Show success message
        flash(__(':name updated successfully', ['name' => $this->front->label]))->success();

        // Redirect if there was a redirect value on the form
        if ($this->request->filled('redirect_url')) {
            return redirect($this->request->redirect_url);
        }

        // Return the created object
        return $this->object;
    }
}
