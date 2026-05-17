<?php

namespace WeblaborMx\Front\Inputs;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Text extends Input
{
    public function form()
    {
        return html()
            ->text($this->getColumn(), $this->getDefaultValue())
            ->attributes($this->attributes);
    }

    public function excelFormat(): ?string
    {
        return $this->excel_type ?? NumberFormat::FORMAT_TEXT;
    }
}
