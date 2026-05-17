<?php

namespace WeblaborMx\Front\Inputs;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ID extends Input
{
    public $show_on_edit = false;

    public $show_on_create = false;

    public function __construct($title = null, $column = null, $extra = null, $source = null)
    {
        parent::__construct($title, $column, $extra, $source);
        if (is_null($title)) {
            $this->title = 'ID';
        }
    }

    public function form() {}

    public function getValue($object)
    {
        return $object->getKey();
    }

    public function getExcelValue($object)
    {
        return $object->getKey();
    }

    public function excelFormat(): ?string
    {
        return $this->excel_type ?? NumberFormat::FORMAT_NUMBER;
    }
}
