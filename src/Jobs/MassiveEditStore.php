<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Support\Str;

class MassiveEditStore
{
    public $front;
    public $object;
    public $key;
    public $request;
    public $input_front;

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
        if (!isset($this->front->showRelations()[$this->key])) {
            abort(406, 'Key isnt correct');
        }

        // Get relationship input
        $input = $this->front->showRelations()[$this->key];
        $input_front = $input->front->addData($this->front->data);
        $this->input_front = $input_front;

        // Get data that should be added to all data
        $basic_data = collect($this->request->except(['_token', 'submitName', 'relation_front', 'relation_id', 'redirect_url', 'relation_url']))->filter(function ($item) {
            return !is_array($item) && !is_null($item);
        });

        // Get request data
        $data = $this->request->all();

        // Modify data if exists a massive class
        if (is_object($input->massive_class)) {
            $data = $input->massive_class->processData($data);
        }

        // Save data on table
        $this->saveData($data, $input, $this->object, $basic_data);

        // Declare it as null
        $return = null;

        // If another button is pressed is because the presence of massive class
        if (isset($this->request->submitName) && is_object($input->massive_class)) {
            $function = $this->request->submitName;
            $return = $input->massive_class->$function($this->object, $data);
        }

        // Show successfull message
        if (is_null($return)) {
            flash(__(':name updated successfully', ['name' => $this->front->plural_label]))->success();
        }
    }

    private function saveData($data, $input, $object, $basic_data)
    {
        $objects = collect($data)->filter(function ($item) {
            // Just show arrays
            return is_array($item);
        })->filter(function ($item, $key) {
            // Avoid adding data with only nulls
            $values = collect($item)->values()->whereNotNull();

            // Get required fields
            $required_fields = collect($this->input_front->getRules('index'))->filter(function ($item) {
                return in_array('required', $item);
            })->keys();

            // Check if required fields have a value
            $required_fields_exist = $required_fields->mapWithKeys(function ($column) use ($item) {
                return [$column => isset($item[$column])];
            });

            // Validation
            $required_values_result = $required_fields_exist->values()->unique();
            $has_required_values = $required_fields->count() == 0 || ($required_values_result->count() == 1 && $required_values_result->first() == true);

            // Show message error if is not a new field
            if (!$has_required_values && !Str::contains($key, 'new')) {
                $missing_columns = $required_fields_exist->filter(function ($item) {
                    return !$item;
                })->keys()->implode(', ');
                flash()->warning('Row '.$key.' need the next required fields: '.$missing_columns.'. Update ignored.');
            }
            return $values->count() > 1 && $has_required_values;
        })->map(function ($data, $key) use ($input, $object, $basic_data) {
            $data = $this->input_front->processData($data);

            // If there is a new column save it instead of updating
            if (Str::contains($key, 'new')) {
                $data = $basic_data->merge($data)->toArray();
                $model = $input->front->model;
                $object = $model::create($data);

                // Call the action to be done after is created
                $this->input_front->store($object, $data);
                return $object;
            }

            // Get models data
            $results = $input->getResults($object);

            // If results is not a query get it individually
            try {
                $item = $results->find($key);
            } catch (\Exception $e) {
                $model = $this->input_front->model;
                $item = $model::find($key);
            }

            // Call the action to be done before is updated
            $this->input_front->beforeUpdate($item, $data);

            // Update data
            $item->update($data);

            // Call the action to be done after is updated
            $this->input_front->update($item, $data);

            return $item;
        });

        // Call the action to be done after is updated
        $this->input_front->afterMassive($objects);
    }
}
