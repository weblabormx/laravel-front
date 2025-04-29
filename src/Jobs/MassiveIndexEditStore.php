<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Support\Str;

class MassiveIndexEditStore
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
    public function __construct($front, $request)
    {
        $this->front = $front;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get data that should be added to all data
        $basic_data = collect($this->request->except(['_token', 'submitName', 'relation_front', 'relation_id', 'redirect_url']))->filter(function ($item) {
            return !is_array($item) && !is_null($item);
        });

        // Get request data
        $data = $this->request->all();

        // Modify data if exists a massive class
        if (is_object($this->front->massive_class)) {
            $data = $this->front->massive_class->processData($data);
        }

        // Save data on table
        $this->saveData($data, $basic_data);

        // Declare it as null
        $return = null;

        // If another button is pressed is because the presence of massive class
        if (isset($this->request->submitName) && is_object($this->front->massive_class)) {
            $function = $this->request->submitName;
            $return = $this->front->massive_class->$function($data);
        }

        // Show successfull message
        if (is_null($return)) {
            flash(__(':name updated successfully', ['name' => $this->front->plural_label]))->success();
        }
    }

    private function saveData($data, $basic_data)
    {
        $objects = collect($data)->filter(function ($item) {
            // Just show arrays
            return is_array($item);
        })->filter(function ($item, $key) {
            // Avoid adding data with only nulls
            $values = collect($item)->values()->whereNotNull();

            // Get required fields
            $required_fields = collect($this->front->getRules('index'))->filter(function ($item) {
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
        })->map(function ($data, $key) use ($basic_data) {
            // If there is a new column save it instead of updating
            if (Str::contains($key, 'new')) {
                $data = $basic_data->merge($data)->toArray();
                $model = $this->front->model;
                $object = $model::create($data);

                // Call the action to be done after is created
                $this->front->store($object, $data);
                return $object;
            }

            // Get models data
            $results = $this->front->globalIndexQuery()->limit($this->front->pagination)->get();

            // If results is not a query get it individually
            try {
                $item = $results->find($key);
            } catch (\Exception $e) {
                $model = $this->front->model;
                $item = $model::find($key);
            }

            // Call the action to be done before is updated
            $this->front->beforeUpdate($item, $data);

            // Update data
            $item->update($data);

            // Call the action to be done after is updated
            $this->front->update($item, $data);

            return $item;
        });

        // Call the action to be done after is updated
        $this->front->afterMassive($objects);
    }
}
