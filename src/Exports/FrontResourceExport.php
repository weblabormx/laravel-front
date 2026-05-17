<?php

namespace WeblaborMx\Front\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FrontResourceExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        private $front,
        private array $columns,
    ) {}

    public function collection()
    {
        $fields = $this->front->exportableIndexFields($this->columns);
        $query = $this->front->applyIndexSorting($this->front->globalIndexQuery());

        return $query->get()->map(function ($object) use ($fields) {
            $this->front->setObject($object);

            return $fields->map(function ($field) use ($object) {
                return strip_tags((string) $field->getValue($object));
            })->values();
        });
    }

    public function headings(): array
    {
        return $this->front->exportableIndexFields($this->columns)
            ->pluck('title')
            ->all();
    }
}
