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
        $title = request()->filled('search_field') ? request()->search_field : $this->front->search_title;

        // Get results query
        $result = $this->front->globalIndexQuery();

        // Get query if sent
        if ($this->request->filled('filter_query')) {
            $query = json_decode($this->request->filter_query);
            $query = unserialize($query);
            $query = $query->getClosure();
            $result = $query($result);
        }

        // Get search filter
        $filter = config('front.default_search_filter');
        $search_filter = new $filter();

        // Search results and map with format
        $result = $search_filter->apply($result, $this->request->term);
        $result = $result->limit($this->front->search_limit)->get()->map(function ($item) use ($title) {
            return [
                'label' => $item->$title,
                'id' => $item->getKey(),
                'value' => $item->$title
            ];
        })->sortBy('label');

        // If there are results so print
        if ($result->count() > 0) {
            print json_encode($result);
            return;
        }

        // If there aren't results show that nothing was found
        $result = [[
            'label' => __('Nothing found'),
            'id' => 0,
            'value' => __('Nothing found')
        ]];
        print json_encode($result);
    }
}
