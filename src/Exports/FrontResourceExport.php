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

    public function __construct(
        private $front,
        private array $columns,
    ) {}

    public function collection()
    {
        $fields = $this->fields();
        $query = $this->front->applyIndexSorting($this->front->globalIndexQuery());
        $objects = $query->get();
        $this->row_count = $objects->count();

        return $objects->map(function ($object) use ($fields) {
            $this->front->setObject($object);

            return $fields->map(function ($field) use ($object) {
                $value = $field->getExcelValue($object);

                return is_string($value) ? trim(strip_tags($value)) : $value;
            })->values();
        });
    }

    public function headings(): array
    {
        return $this->fields()
            ->pluck('title')
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

            $formula = '"'.str_replace('"', '""', implode(',', $options)).'"';
            if (strlen($formula) > 255) {
                continue;
            }

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
}
