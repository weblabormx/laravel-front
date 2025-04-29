<?php

namespace WeblaborMx\Front\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class PartialIndex
{
    private $front;
    private $result;
    private $page_name;
    private $headers;
    private $rows;
    private $show_filters;
    public $show_actions;

    public function __construct($front, $result, $page_name = 'page', $show_filters = false)
    {
        $this->front          = $front;
        $this->result         = $result;
        $this->page_name      = $page_name;
        $this->show_filters   = $show_filters;

        // If in front there is show_actions, use that
        if (isset($this->front->show_actions)) {
            $this->show_actions = $this->front->show_actions;

            // If not check if there are enough actions to show
        } elseif (!isset($this->show_actions)) {
            $this->show_actions = in_array('show', $front->actions) || in_array('edit', $front->actions) || in_array('destroy', $front->actions);
        }

        // Check if individual object has permissions
        if (isset($front) && isset($front->related_object) && isset($front->related_object->block_edition)) {
            $this->show_actions = !$front->related_object->block_edition && $this->show_actions;
        }

        if ($this->result->count() > 0) {
            $this->getUnusedColumns();
        }
        return $this;
    }

    public function links()
    {
        if (!$this->result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return;
        }
        $appends = collect(request()->except($this->page_name))->map(function ($item) {
            if (is_null($item)) {
                $item = '';
            }
            return $item;
        })->all();
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
        if (!$this->show_filters || count($this->front->filters()) <= 0) {
            return;
        }
        $front = $this->front;
        return view('front::elements.relation_filters', compact('front'));
    }

    public function totals()
    {
        if (Str::endsWith(get_class($this->result), 'Collection')) {
            $total = $this->result->count();
        } else {
            $total = $this->result->total();
        }
        return view('front::elements.total_results', compact('total'));
    }

    public function calculateHeaders()
    {
        $this->front->setObject($this->result->first());
        $result = collect([]);
        foreach ($this->front->indexFields() as $field) {
            $input = [];
            $input['class'] = $field->data_classes;
            $input['title'] = $field->title;
            $input['column'] = $field->column;
            $result[] = (object) $input;
        }
        return $result;
    }

    public function calcuateRows()
    {
        return $this->result->map(function ($object) {
            $this->front->setObject($object);
            $columns = $this->front->indexFields()->map(function ($column) use ($object) {
                $input = [];
                $input['class'] = $column->data_classes;
                $input['title'] = $column->title;
                $input['value'] = $column->getValueProcessed($object);
                $input['column'] = $column->column;
                $input['extra_data'] = $column->extra_data;
                $input['object'] = $column;
                return (object) $input;
            })->values();
            return (object) compact('columns', 'object');
        });
    }

    private function getUnusedColumns()
    {
        $headers = $this->calculateHeaders();
        $rows = $this->calcuateRows();

        $unused_columns = $rows->pluck('columns')->map(function ($column) {
            $column = $column->filter(function ($item) {
                return $item->value === '--';
            })->keys();
            return $column;
        })->flatten()->countBy()->filter(function ($item) use ($rows) {
            $total_items = $rows->count();
            return $item == $total_items;
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
        if (count($this->front->index_views) <= 1) {
            return;
        }

        // Add url and name on views
        $views = collect($this->front->index_views)->map(function ($item, $key) {
            $data = $item;
            $data['name'] = $key;
            $data['url'] = $this->getViewUrl($data['name']);
            return $data;
        })->values();

        // Get current view
        $current_view = $this->front->getCurrentViewName();

        // Set is active
        $views = $views->map(function ($item) use ($current_view) {
            $item['is_active'] = $current_view == $item['name'];
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

        $query = parse_url($url, PHP_URL_QUERY);
        // Returns a string if the URL has parameters or NULL if not
        if ($query) {
            $url .= '&'.$name.'='.$view;
        } else {
            $url .= '?'.$name.'='.$view;
        }

        $url = str_replace('?&', '?', $url);
        $url = str_replace('??', '?', $url);
        return $url;
    }
}
