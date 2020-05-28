<?php

namespace WeblaborMx\Front\Helpers;

class PartialIndex
{
    private $front;
    private $result;
    private $page_name;
    private $headers;
    private $rows;

	public function __construct($front, $result, $page_name = 'page')
    {
        $this->front          = $front;
        $this->result         = $result;
        $this->page_name      = $page_name;
        
        $this->getUnusedColumns();
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
}
