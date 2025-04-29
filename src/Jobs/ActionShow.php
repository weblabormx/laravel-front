<?php

namespace WeblaborMx\Front\Jobs;

use WeblaborMx\Front\Traits\IsRunable;

class ActionShow
{
    use IsRunable;

    public $front;
    public $object;
    public $action;
    public $store;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $object, $action, $store)
    {
        $this->front = $front;
        $this->object = $object;
        $this->action = $action;
        $this->store = $store; // Function to execute in case there aren't any fields
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $object = $this->object;

        // Modify with indexResult
        if (isset($object)) {
            $result = collect([$object]);
            $result = $this->front->indexResult($result);
            $object = $result->first();
        }

        // Search the individual action
        if (isset($object)) {
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

        // Set object to action
        if (isset($object)) {
            $action = $action->setObject($object);
        }

        $result = $action->fields();

        // If returns a response so dont do any more
        if (isResponse($result)) {
            return $result;
        }

        // If doesnt have fields return action
        if (!is_array($result)) {
            return $action;
        }

        // Detect if dont have fields or if are just hidden inputs process inmediately
        $visible_fields = collect($result)->filter(function ($item) {
            return get_class($item) != 'WeblaborMx\Front\Inputs\Hidden';
        });
        if ($visible_fields->count() == 0) {
            $function = $this->store;
            return $function();
        }

        // Returns action
        return $action;
    }
}
