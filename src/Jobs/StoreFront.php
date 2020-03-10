<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Queue\SerializesModels;

class StoreFront
{
    use DispatchAndReturn, SerializesModels;

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
        $data = $this->front->processData($this->request->all());
        $this->front->validate($data);

        $model = $this->front->getModel();
        $object = $model::create($data);
        $this->front->store($object, $this->request);
        return $this->addResponse($object);
    }
}