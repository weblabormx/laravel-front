<?php

namespace WeblaborMx\Front\Jobs;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
        if (isset($redirect_url)) {
            return redirect($redirect_url);
        }

        // Get results
        $objects = $this->front->globalIndexQuery();

        // Detect if crud is just for 1 item and redirects
        if (!Str::contains(get_class($objects), 'Illuminate\Database\Eloquent')) {
            $url = $this->base.'/'.$objects->getKey().'/edit';
            return redirect($url);
        }

        // If is normal query paginate results
        $result = $this->paginate($objects);

        // If the result needs to be modified just modify it
        $result = $this->result($result);

        // If filter has different default values check who is not empty
        $result = $this->multipleRedirects($result);

        // Call the action to be done after is accessed
        $this->front->index();

        return $result;
    }

    private function multipleRedirects($result)
    {
        // If doesnt have results and is redirect
        if (request()->filled('is_redirect') && $result->count() == 0) {
            // Get url to send
            $redirect_url = $this->front->redirects(false);

            // If there isn't any redirect url don't do anything
            if (!isset($redirect_url) || url()->full() == $redirect_url) {
                return $result;
            }

            // Generate new url
            $current_query = request()->query();
            $new_query = explode('?', $redirect_url)[1];
            $new_query = collect(explode('&', $new_query))->mapWithKeys(function ($item) {
                $item = explode('=', $item);
                return [$item[0] => $item[1]];
            })->toArray();

            // Send to new url
            if (isset($redirect_url) && $current_query != $new_query) {
                return redirect($redirect_url);
            }
        }
        return $result;
    }

    private function paginate($objects)
    {
        // Get cache time to cache
        $cache = $this->front->cacheFor();

        // If not time set so paginate directly
        if ($cache == false || !in_array('indexQuery', $this->front->cache)) {
            return $objects->paginate($this->front->pagination);
        }

        // Get cache key
        $cache_key = $this->getPaginateCacheKey($objects);

        // Make the pagination
        return Cache::remember($cache_key, $cache, function () use ($objects) {
            return $objects->paginate($this->front->pagination);
        });
    }

    private function getPaginateCacheKey($objects)
    {
        $request = collect(request()->all())->whereNotNull()->map(function ($item, $key) {
            return $key.'-'.$item;
        })->implode(',');
        $key = 'front:'.$request.':';
        $key .= $objects->toSql();
        $key = hash('sha256', $key);
        return $key;
    }

    public function result($result)
    {
        // Get cache time to cache
        $cache = $this->front->cacheFor();

        // If not time set so paginate directly
        if ($cache == false || !in_array('indexResult', $this->front->cache)) {
            return $this->front->indexResult($result);
        }

        // Get cache key
        $cache_key = $this->getResultCacheKey($result);

        // Make the pagination
        return Cache::remember($cache_key, $cache, function () use ($result) {
            return $this->front->indexResult($result);
        });
    }

    private function getResultCacheKey($result)
    {
        $key_name = $result->first()->getKeyName();
        $key = get_class($this).':result:'.$result->pluck($key_name)->implode('|');
        $key = hash('sha256', $key);
        return $key;
    }
}
