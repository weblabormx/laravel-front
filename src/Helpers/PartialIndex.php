<?php

namespace WeblaborMx\Front\Helpers;

use Illuminate\Support\Str;

class PartialIndex
{
    private $front;
    private $result;
    private $page_name;
    private $headers;
    private $rows;
    private $show_filters;

	public function __construct($front, $result, $page_name = 'page', $show_filters = false)
    {
        $this->front          = $front;
        $this->result         = $result;
        $this->page_name      = $page_name;
        $this->show_filters   = $show_filters;
        
        if($this->result->total()>0) {
            $this->getUnusedColumns();
        }
        return $this;
    }

    public function links()
    {
        if(!$this->result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return;
        }
        $appends = request()->except($this->page_name);
        return $this->result->appends($appends)->links();
    }

    public function headers()
    {
        return $this->headers;
    }

    public function rows()
    {
        return $this->rows;
    }

    public function filters()
    {
        // Show if set by relationship
        if(!$this->show_filters || count($this->front->filters()) <= 0) {
            return;
        }
        $front = $this->front;
        return view('front::elements.relation_filters', compact('front'));
    }

    public function totals()
    {
        $result = $this->result;
        return view('front::elements.total_results', compact('result'));
    }

    public function calcuateHeaders()
    {
        $this->front->setObject($this->result->first());
        $result = collect([]);
        foreach($this->front->indexFields() as $field) {
            $input = [];
            $input['class'] = $field->data_classes;
            $input['title'] = $field->title;
            $result[] = (object) $input;
        }
        return $result;
    }

    public function calcuateRows()
    {
        return $this->result->map(function($object) {
            $this->front->setObject($object);
            $columns = $this->front->indexFields()->map(function($column) use ($object) {
                $input = [];
                $input['class'] = $column->data_classes;
                $input['title'] = $column->title;
                $input['value'] = $column->getValueProcessed($object);
                return (object) $input;
            })->values();
            return (object) compact('columns', 'object');
        });
    }

    private function getUnusedColumns()
    {
        $headers = $this->calcuateHeaders();
        $rows = $this->calcuateRows();
        
        $unused_columns = $rows->pluck('columns')->map(function($column) {
            $column = $column->filter(function($item) {
                return $item->value=='--';
            })->keys();
            return $column;
        })->flatten()->countBy()->filter(function($item) use ($rows) {
            $total_items = $rows->count();
            return $item==$total_items;
        })->keys();

        foreach ($unused_columns as $key) {
            unset($headers[$key]);
            foreach ($rows as &$row) {
                unset($row->columns[$key]);
            }
        }

        $this->headers = $headers;
        $this->rows = $rows;
    }

    public function views()
    {
        if(count($this->front->index_views) <= 1) {
            return;
        }

        // Add url and name on views
        $views = collect($this->front->index_views)->map(function($item, $key) {
            $data = $item;
            $data['name'] = $key;
            $data['url'] = $this->getViewUrl($data['name']);
            return $data;
        })->values();

        // Get current view
        $current_view = $this->front->getCurrentViewName();

        // Set is active
        $views = $views->map(function($item) use ($current_view) {
            $item['is_active'] = $current_view==$item['name'];
            return $item;
        });

        return view('front::elements.views_buttons', compact('views'));
    }

    private function getViewUrl($view)
    {
        $name = Str::snake(class_basename(get_class($this->front)));
        $name .= '_view';

        $url = request()->fullUrl();
        $url = preg_replace('~(\?|&)'.$name.'=[^&]*~', '$1', $url);
        $url = str_replace('?&', '?', $url);

        $query = parse_url($url, PHP_URL_QUERY);
        // Returns a string if the URL has parameters or NULL if not
        if ($query) {
            $url .= '&'.$name.'='.$view;
        } else {
            $url .= '?'.$name.'='.$view;
        }
        return $url;
    }
}
