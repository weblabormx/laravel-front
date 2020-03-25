<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Support\Str;

class MassiveEditStore
{
    public $front;
    public $object;
    public $key;
    public $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $object, $key, $request)
    {
        $this->front = $front;
        $this->object = $object;
        $this->key = $key;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if relationship exists
        if(!isset($this->front->showRelations()[$this->key])) {
            abort(406, 'Key isnt correct');
        }

        // Get relationship input
        $input = $this->front->showRelations()[$this->key];
        $input_front = $input->front->addData($this->front->data);

        // Get data that should be added to all data
        $basic_data = collect($this->request->except(['_token', 'submitName']))->filter(function($item) {
            return !is_array($item) && !is_null($item);
        });

        // Get request data
        $data = $this->request->all();

        // Modify data if exists a massive class
        if(is_object($input->massive_class)) {
            $data = $input->massive_class->processData($data);
        }

        // Save data on table
        $this->saveData($data, $input, $this->object, $basic_data, $input_front);

        // Declare it as null
        $return = null;

        // If another button is pressed is because the presence of massive class
        if(isset($this->request->submitName) && is_object($input->massive_class)) {
            $function = $this->request->submitName;
            $return = $input->massive_class->$function($this->object, $data);
        }

        // Show successfull message
        if(is_null($return)) {
            flash(__(':name updated successfully', ['name' => $this->front->plural_label]))->success();
        }
    }

    private function saveData($data, $input, $object, $basic_data, $input_front) 
    {
        collect($data)->filter(function($item) {
            // Just show arrays
            return is_array($item);
        })->filter(function($item) {
            // Avoid adding data with only nulls
            $item = collect($item)->values()->unique();
            return $item->count() > 1; 
        })->each(function($data, $key) use ($input, $object, $basic_data, $input_front) {
            // If there is a new column save it instead of updating
            if(Str::contains($key, 'new')) {
                $data = $basic_data->merge($data)->toArray();
                $model = $input->front->model;
                $model::create($data);
                return;
            }

            // Get models data
            $results = $input->getResults($object);

            // If results is not a query get it individually
            if(method_exists($results, 'find')) {
                $item = $results->find($key);    
            } else {
                $model = $input_front->model;
                $item = $model::find($key);
            }

            // Update data
            $item->update($data);
        });
    }
}