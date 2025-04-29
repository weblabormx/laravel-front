<?php

namespace WeblaborMx\Front\Jobs;

use WeblaborMx\Front\Traits\IsRunable;

class ActionStore
{
    use IsRunable;

    public $front;
    public $object;
    public $action;
    public $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $object, $action, $request)
    {
        $this->front = $front;
        $this->object = $object;
        $this->action = $action;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Search the individual action
        if (isset($this->object)) {
            $action = $this->front->searchAction($this->action);
        } else {
            $action = $this->front->searchIndexAction($this->action);
        }

        // Show message if wasn't found
        if (!is_object($action)) {
            abort(406, "Action wasn't found: {$this->action}");
        }

        // Dont show action if is not showable
        if (!$action->show) {
            abort(404);
        }

        // Set front
        $action = $action->setFront($this->front);

        // Set object to action and validate
        if (isset($this->object)) {
            $action = $action->setObject($this->object);
        }

        // Get data to be saved
        $data = $action->processData($this->request->all());

        // Validate object
        $action->validate($data);

        // Process data after validation
        $data = $action->processDataAfterValidation($data);

        // Get request
        $request = $this->request;
        $request = $request->merge($data);

        // Execute action
        if (isset($this->object)) {
            $result = $action->handle($this->object, $request);
        } else {
            $result = $action->handle($request);
        }

        // If is front fields
        if ($this->isFrontable($result)) {
            $result = $this->makeFrontable($result, [
                'title' => $action->title,
            ], $this->front);
        }

        // If returns a response so dont do any more
        if (isResponse($result)) {
            $this->request->flash();
            return $result;
        }

        // If returns empty so show successfull message
        if (!isset($result)) {
            flash(__(':name action executed successfully', ['name' => $action->title,]))->success();
        }

        // In case they return a flash answer
        $this->request->flash();
    }
}
