<?php

namespace WeblaborMx\Front\Texts;

use WeblaborMx\Front\Front;

class Table extends Text
{
	public $data;
	public $headers;

	public function load()
	{
		$this->data = $this->title;
		if (isset($this->column) && is_array($this->column)) {
			$this->headers = $this->column;
		}
		if (!isset($this->headers)) {
			$this->headers = $this->detectHeaders();
		}
		$this->orderData();
	}

	protected function detectHeaders()
	{
		return collect($this->data)->collapse()->keys();
	}

	protected function orderData()
	{
		$this->data = collect($this->data)->map(function ($item) {
			$row = [];
			foreach ($this->headers as $header) {
				$row[$header] = isset($item[$header]) ? $item[$header] : '--';
			}
			return $row;
		});
	}

	public function form()
	{
		$table = $this;
		return view('front::texts.table', compact('table'))->render();
	}
}
