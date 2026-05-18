<?php

namespace WeblaborMx\Front\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class FrontResourceExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings
{
    private int $row_count = 0;
    private int $validation_column_index = 1;
    private $validation_sheet;

    public function __construct(
        private $front,
        private array $columns,
    ) {
        $this->columns = $front->exportColumnKeys($columns);
    }

    public function collection()
    {
        $fields = $this->fields();
        $query = $this->front->applyIndexSorting($this->front->globalIndexQuery());
        $objects = $query->get();
        $this->row_count = $objects->count();

        return $objects->map(function ($object) use ($fields) {
            $this->front->setObject($object);

            $data = $fields->map(function ($field) use ($object) {
                $value = $field->getExcelValue($object);

                return is_string($value) ? trim(strip_tags($value)) : $value;
            })->values()->all();

            return $this->front->processExcel($data, 'export', null, $object);
        });
    }

    public function headings(): array
    {
        return $this->fields()
            ->map(function ($field) {
                return $field->title;
            })
            ->all();
    }

    public function columnFormats(): array
    {
        return $this->fields()
            ->mapWithKeys(function ($field, $index) {
                $format = $field->excelFormat();
                if (is_null($format) || $format === '') {
                    return [];
                }

                return [Coordinate::stringFromColumnIndex($index + 1) => $format];
            })
            ->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->applySelectValidations($event);
            },
        ];
    }

    private function fields()
    {
        return $this->front->exportableIndexFields($this->columns);
    }

    private function applySelectValidations(AfterSheet $event): void
    {
        foreach ($this->fields()->values() as $index => $field) {
            $options = $field->excelOptions();
            if (count($options) === 0) {
                continue;
            }

            $formula = $this->selectValidationFormula($event, $options);

            $column = Coordinate::stringFromColumnIndex($index + 1);
            $last_row = max($this->row_count + 1, 2);

            for ($row = 2; $row <= $last_row; $row++) {
                $validation = $event->sheet->getCell($column.$row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formula);
            }
        }
    }

    private function selectValidationFormula(AfterSheet $event, array $options)
    {
        $formula = '"'.str_replace('"', '""', implode(',', $options)).'"';
        if (strlen($formula) <= 255) {
            return $formula;
        }

        return $this->validationRangeFormula($event, $options);
    }

    private function validationRangeFormula(AfterSheet $event, array $options)
    {
        $sheet = $this->validationSheet($event);
        $column = Coordinate::stringFromColumnIndex($this->validation_column_index);

        foreach (array_values($options) as $index => $option) {
            $sheet->setCellValue($column.($index + 1), $option);
        }

        $this->validation_column_index++;

        return "'".$sheet->getTitle()."'!".'$'.$column.'$1:$'.$column.'$'.count($options);
    }

    private function validationSheet(AfterSheet $event)
    {
        if ($this->validation_sheet) {
            return $this->validation_sheet;
        }

        $spreadsheet = $event->sheet->getDelegate()->getParent();
        $this->validation_sheet = $spreadsheet->createSheet();
        $this->validation_sheet->setTitle('__front_options');
        $this->validation_sheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        return $this->validation_sheet;
    }
}
