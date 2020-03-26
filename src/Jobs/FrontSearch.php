<?php

namespace WeblaborMx\Front\Jobs;

class FrontSearch
{
    public $front;
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
        // Get title column for element
        $title = $this->front->title;

        // Get results query
        $result = $this->front->globalIndexQuery();

        // Get query if sent
        if($this->request->filled('filter_query')) {
            $query = json_decode($this->request->filter_query);
            $query = unserialize($query);
            $query = $query->getClosure();
            $result = $query($result);
        }

        // Search results and map with format
        $result = $result->search($this->request->term)->limit(10)->get()->map(function($item) use ($title) {
            return [
                'label' => $item->$title, 
                'id' => $item->getKey(), 
                'value' => $item->$title 
            ];
        })->sortBy('label');

        // If there are results so print
        if($result->count() > 0) {
            print json_encode($result);
            return;
        }

        // If there aren't results show that nothing was found
        $result = [[
            'label' => __('Nothing founded'), 
            'id' => 0, 
            'value' => __('Nothing founded')
        ]];
        print json_encode($result);
    }
}