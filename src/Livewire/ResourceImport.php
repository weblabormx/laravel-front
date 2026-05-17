<?php

namespace WeblaborMx\Front\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Throwable;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Imports\FrontResourceImport as Importer;

class ResourceImport extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    private const IndexSource = 'index';
    #[Locked]
    public $resource;
    public $import_file;
    public $import_preview = [];
    public $import_summary = null;
    public $import_structure_errors = [];
    public $import_headings = [];
    public $import_heading_labels = [];
    public $import_extra_headings = [];
    public $import_sheet_index = 0;
    public $analyzed = false;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
        $front = $this->front();

        $this->authorizeImport($front);
    }

    public function updatedImportFile(): void
    {
        $this->import_headings = [];
        $this->import_heading_labels = [];
        $this->import_extra_headings = [];
        $this->import_sheet_index = 0;
        $this->import_preview = [];
        $this->import_structure_errors = [];
        $this->import_summary = null;
        $this->analyzed = false;
    }

    public function front()
    {
        return Front::makeResource($this->resource)->setSource(self::IndexSource);
    }

    public function analyzeImport(): void
    {
        $this->authorizeImport($this->front());
        $this->validateImportFile();

        $file = null;
        try {
            $file = $this->localImportFile();
            $this->extractHeadings($file['path']);
        } catch (Throwable $throwable) {
            report($throwable);
            $this->import_headings = [];
            $this->import_heading_labels = [];
            $this->import_preview = $this->buildImportPreview();
            $this->import_structure_errors = [
                __('front::messages.unreadable_file'),
            ];
            $this->import_summary = null;
            $this->analyzed = true;

            return;
        } finally {
            $this->deleteLocalImportFile($file);
        }

        $this->import_preview = $this->buildImportPreview();
        $this->import_extra_headings = $this->buildExtraHeadings();
        $this->import_structure_errors = $this->buildImportStructureErrors();
        $this->import_summary = null;
        $this->analyzed = true;
    }

    public function runImport(): void
    {
        $this->authorizeImport($this->front());
        $this->validateImportFile();

        if (!$this->analyzed) {
            $this->analyzeImport();
        }

        if (!$this->canImport()) {
            return;
        }

        $file = null;
        try {
            $file = $this->localImportFile();
            $import = new Importer($this->resource, $this->importColumnKeys(), $this->import_sheet_index);
            Excel::import($import, $file['path']);
        } catch (Throwable $throwable) {
            report($throwable);
            $this->import_summary = [
                'imported' => 0,
                'ignored' => 0,
                'errors' => [
                    [
                        'row' => '-',
                        'message' => __('front::messages.unreadable_file'),
                    ],
                ],
            ];

            return;
        } finally {
            $this->deleteLocalImportFile($file);
        }

        $this->import_summary = [
            'imported' => $import->imported,
            'ignored' => $import->ignored,
            'errors' => $import->errors,
        ];
    }

    public function importColumnKeys(): array
    {
        $front = $this->front();
        $columns = [];

        foreach ($front->configurableIndexFields() as $index => $field) {
            $columns[] = $front->indexColumnKey($field, $index);
        }

        return $columns;
    }

    public function importUrl(): string
    {
        return $this->front()->getBaseUrl().'/import';
    }

    public function canImport(): bool
    {
        return $this->analyzed && count($this->import_structure_errors) === 0;
    }

    private function validateImportFile(): void
    {
        $this->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);
    }

    private function authorizeImport($front): void
    {
        if (!$front->enable_import) {
            abort(403, __('This action is unauthorized.'));
        }

        $this->authorize('viewAny', $front->getModel());
        $this->frontAuthorize($front, self::IndexSource);
    }

    private function frontAuthorize($front, string $method): void
    {
        if (!in_array($method, $front->actions)) {
            abort(403, __('This action is unauthorized.'));
        }
    }

    private function buildImportPreview(): array
    {
        $front = $this->front();
        $fields = $front->configurableIndexFieldsForColumns($this->importColumnKeys());
        $importable = $front->importableIndexFields($this->importColumnKeys());
        $importableKeys = $importable->pluck('front_column_key')->all();
        $preview = [
            [
                'title' => $front->excelIdHeading(),
                'status' => in_array($front->excelIdHeadingKey(), $this->import_headings) ? 'importable' : 'missing',
            ],
        ];

        foreach ($fields as $field) {
            $isImportable = in_array($field->front_column_key, $importableKeys);
            $isPresent = count(array_intersect($front->excelHeadingsForField($field), $this->import_headings)) > 0;

            $preview[] = [
                'title' => $field->title,
                'status' => match (true) {
                    !$isImportable => 'ignored',
                    !$isPresent => 'missing',
                    default => 'importable',
                },
            ];
        }

        return $preview;
    }

    private function extractHeadings($file)
    {
        $sheets = (new HeadingRowImport)->toArray($file);
        $selected = $this->selectHeadingsSheet($sheets);
        $this->import_sheet_index = $selected['index'];
        $this->import_heading_labels = $selected['labels'];

        $this->import_headings = collect($selected['labels'])
            ->filter()
            ->map(function ($heading) {
                return str($heading)->slug('_')->toString();
            })
            ->unique()
            ->values()
            ->all();
    }

    private function selectHeadingsSheet(array $sheets): array
    {
        foreach ($sheets as $index => $sheet) {
            $labels = $sheet[0] ?? [];
            $headings = $this->normalizeHeadings($labels);

            if (in_array($this->front()->excelIdHeadingKey(), $headings)) {
                return compact('index', 'labels');
            }
        }

        $index = array_key_first($sheets) ?? 0;
        $labels = $sheets[$index][0] ?? [];

        return compact('index', 'labels');
    }

    private function normalizeHeadings(array $labels): array
    {
        return collect($labels)
            ->filter()
            ->map(function ($heading) {
                return str($heading)->slug('_')->toString();
            })
            ->unique()
            ->values()
            ->all();
    }

    private function buildImportStructureErrors(): array
    {
        $front = $this->front();
        if (!in_array($front->excelIdHeadingKey(), $this->import_headings)) {
            return [
                __('front::messages.missing_excel_id'),
            ];
        }

        $importableHeadings = $front->importableIndexFields($this->importColumnKeys())
            ->map(function ($field) use ($front) {
                return $front->excelHeadingsForField($field);
            })->flatten()
            ->intersect($this->import_headings);

        if ($importableHeadings->isNotEmpty()) {
            return [];
        }

        return [
            __('front::messages.no_importable_columns'),
        ];
    }

    private function buildExtraHeadings(): array
    {
        $labels = collect($this->import_heading_labels)->mapWithKeys(function ($label) {
            return [str($label)->slug('_')->toString() => $label];
        });

        return collect($this->import_headings)
            ->diff($this->knownHeadings())
            ->map(function ($heading) use ($labels) {
                return $labels->get($heading, $heading);
            })
            ->values()
            ->all();
    }

    private function knownHeadings(): array
    {
        $front = $this->front();
        $headings = collect([$front->excelIdHeadingKey()]);

        $front->configurableIndexFieldsForColumns($this->importColumnKeys())->each(function ($field) use ($front, $headings) {
            $headings->push($front->excelHeadingsForField($field));
        });

        return $headings->flatten()->unique()->values()->all();
    }

    private function localImportFile()
    {
        $path = $this->import_file->getRealPath();
        if (is_string($path) && is_file($path)) {
            return [
                'path' => $path,
                'temporary' => false,
            ];
        }

        $extension = $this->import_file->getClientOriginalExtension() ?: 'xlsx';
        $base = tempnam(sys_get_temp_dir(), 'front-import-');
        $temporary = $base.'.'.$extension;
        rename($base, $temporary);
        file_put_contents($temporary, $this->import_file->get());

        return [
            'path' => $temporary,
            'temporary' => true,
        ];
    }

    private function deleteLocalImportFile($file): void
    {
        if (is_array($file) && ($file['temporary'] ?? false) && is_file($file['path'])) {
            @unlink($file['path']);
        }
    }

    public function render()
    {
        return view('front::livewire.resource-import');
    }
}
