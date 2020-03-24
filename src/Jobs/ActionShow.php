<?php

namespace WeblaborMx\Front\Jobs;

class ActionShow
{
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
        // Search the individual action
        $action = $this->front->searchAction($this->action);
        if(!is_object($action)) {
            abort(406, "Action wasn't found: {$this->action}");
        }

        // Dont show action if is not showable
        if(!$action->show) {
            abort(404);
        }

        // Set object to action
        $action = $action->setObject($this->object);

        // Detect if dont have fields process inmediately
        if(count($action->fields())==0) {
            $function = $this->store;
            return $function();
        }

        // Returns action
        return $action;
    }
}