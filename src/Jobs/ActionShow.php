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
        // Search the individual action
        if(isset($this->object)) {
            $action = $this->front->searchAction($this->action);
        } else {
            $action = $this->front->searchIndexAction($this->action);
        }

        // Show message if wasn't found
        if(!is_object($action)) {
            abort(406, "Action wasn't found: {$this->action}");
        }

        // Dont show action if is not showable
        if(!$action->show) {
            abort(404);
        }

        // Set object to action
        if(isset($this->object)) {
            $action = $action->setObject($this->object);    
        }

        $result = $action->fields();

        // If returns a response so dont do any more
        if($this->isResponse($result)) {
            return $result;
        }

        // Detect if dont have fields process inmediately
        if(is_array($result) && count($result)==0) {
            $function = $this->store;
            return $function();
        }

        // Returns action
        return $action;
    }
}
