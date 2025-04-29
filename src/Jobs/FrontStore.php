<?php

namespace WeblaborMx\Front\Jobs;

use WeblaborMx\Front\Traits\IsRunable;

class FrontStore
{
    use IsRunable;

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
        if (isResponse($data)) {
            return $data;
        }

        // Validate
        $this->front->validate($data);

        // Process data after validation
        $data = $this->front->processDataAfterValidation($data);

        // Process data Before saving
        $data = $this->front->processDataBeforeSaving($data);

        // Make work with arrays
        if (!$this->isArrayOfArrays($data)) {
            $data = [$data];
        }

        // Iterate with all info
        foreach ($data as $result) {
            // Create the object
            $object = $this->front->create($result);

            if (isResponse($object)) {
                return $object;
            }

            // Process actions after save
            $this->front->processAfterSave($object, $this->request);

            // Call the action to be done after is created
            $this->front->store($object, $this->request);
        }

        // Show success message
        flash(__(':name created successfully', ['name' => $this->front->label]))->success();

        // Redirect if there was a redirect value on the form
        if ($this->request->filled('redirect_url')) {
            $url = $this->request->redirect_url;
            $url = str_replace('{base_url}', $this->front->getBaseUrl(), $url);
            $url = str_replace('{key}', $object->getKey(), $url);
            return redirect($url);
        }

        // Return the created object
        return $object;
    }

    private function isArrayOfArrays($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = true;
        foreach ($array as $children) {
            if (!is_array($children)) {
                $result = false;
            }
        }
        return $result;
    }
}
