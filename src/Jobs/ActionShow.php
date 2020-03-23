<?php

namespace WeblaborMx\Front\Jobs;

class ActionShow
{
    public $front;
    public $object;
    public $action;
    public $controller;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $object, $action, $controller)
    {
        $this->front = $front;
        $this->object = $object;
        $this->action = $action;
        $this->controller = $controller;
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
            return $this->controller->actionStore($this->object->getKey(), $this->action, request());
        }

        // Returns action
        return $action;
    }
}