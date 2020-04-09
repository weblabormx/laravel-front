<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Support\Str;

class FrontIndex
{
    public $front;
    public $base;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($front, $base)
    {
        $this->front = $front;
        $this->base = $base;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Redirect if filters asks to do it
        $redirect_url = $this->front->redirects();
        if(isset($redirect_url)) {
            return redirect($redirect_url);
        }

        // Get results
        $objects = $this->front->globalIndexQuery();

        // Detect if crud is just for 1 item and redirects
        if(!Str::contains(get_class($objects), 'Illuminate\Database\Eloquent')) {
            $url = $this->base.'/'.$objects->getKey().'/edit';
            return redirect($url);
        }

        // If is normal query paginate results
        $result = $objects->paginate($this->front->pagination);

        // If the result needs to be modified just modify it
        $result = $this->front->indexResult($result);

        // If filter has different default values check who is not empty
        $result = $this->multipleRedirects($result);

        // Call the action to be done after is accessed
        $this->front->index();

        return $result;
    }

    private function multipleRedirects($result)
    {
        if(request()->filled('is_redirect') && $result->count() == 0) {
            $redirect_url = $this->front->redirects(false);
            $current_query = request()->query();
            $new_query = explode('?', $redirect_url)[1];
            $new_query = collect(explode('&', $new_query))->mapWithKeys(function($item) {
                $item = explode('=', $item);
                return [$item[0] => $item[1]];
            })->toArray();
            if(isset($redirect_url) && $current_query!=$new_query) {
                return redirect($redirect_url);
            }
        }
        return $result;
    }
}
