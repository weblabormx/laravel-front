<?php

namespace WeblaborMx\Front\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use WeblaborMx\Front\Exports\FrontResourceExport;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Imports\FrontResourceImport;
use WeblaborMx\Front\Jobs\FrontIndex;
use WeblaborMx\Front\Traits\IsRunable;

class ResourceIndex extends Component
{
    use AuthorizesRequests;
    use IsRunable;
    use WithFileUploads;

    #[Locked]
    public $resource;

    #[Url(as: 'sort')]
    public $sort = null;

    #[Url(as: 'direction')]
    public $direction;

    public $show_columns = false;

    public $show_import = false;

    public $import_file;

    public $import_preview = [];

    public $import_summary = null;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
        $this->sort = request()->get('sort', $this->sort);
        $this->direction = request()->get('direction', $this->direction) === 'desc' ? 'desc' : 'asc';
        $front = $this->front();

        $this->authorize('viewAny', $front->getModel());
        $this->frontAuthorize($front, 'index');
    }

    public function front()
    {
        return Front::makeResource($this->resource)->setSource('index');
    }

    public function result()
    {
        return $this->indexResponse();
    }

    public function sortBy($column): void
    {
        $front = $this->front();

        if (! $front->enable_index_sorting || ! $front->sortableIndexFields()->has($column)) {
            return;
        }

        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function toggleColumn($column): void
    {
        $front = $this->front();

        if (! $front->enable_column_preferences || ! $this->availableColumns()->has($column)) {
            return;
        }

        $columns = $this->visibleColumnKeys();

        if (in_array($column, $columns)) {
            if (count($columns) <= 1) {
                return;
            }

            $columns = array_values(array_diff($columns, [$column]));
        } else {
            $columns[] = $column;
        }

        $this->saveColumnPreferences($columns, array_unique(array_merge($this->manualColumnKeys(), [$column])));
    }

    public function moveColumn($column, $direction): void
    {
        if (! in_array($direction, ['up', 'down'])) {
            return;
        }

        $front = $this->front();

        if (! $front->enable_column_preferences || ! $this->availableColumns()->has($column)) {
            return;
        }

        $columns = $this->visibleColumnKeys();
        $index = array_search($column, $columns);

        if ($index === false) {
            return;
        }

        $newIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($columns[$newIndex])) {
            return;
        }

        $swapped = $columns[$newIndex];
        $columns[$newIndex] = $column;
        $columns[$index] = $swapped;

        $this->saveColumnPreferences($columns, $this->manualColumnKeys());
    }

    public function resetColumns(): void
    {
        cache()->forget($this->columnPreferenceCacheKey());
    }

    public function export()
    {
        $front = $this->front();

        if (! $front->enable_export) {
            abort(403, 'This action is unauthorized.');
        }

        $this->authorizeIndex($front);
        $this->syncSortRequest();

        return Excel::download(
            new FrontResourceExport($front, $this->visibleColumnKeys()),
            str($front->plural_label)->slug('_')->toString().'.xlsx'
        );
    }

    public function updatedImportFile(): void
    {
        $this->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $this->import_preview = $this->buildImportPreview();
        $this->import_summary = null;
    }

    public function runImport(): void
    {
        $front = $this->front();

        if (! $front->enable_import) {
            abort(403, 'This action is unauthorized.');
        }

        $this->authorizeIndex($front);
        $storeFront = Front::makeResource($this->resource)->setSource('store');
        $this->authorize('create', $storeFront->getModel());
        $this->frontAuthorize($storeFront, 'create');
        $this->frontAuthorize($storeFront, 'store');

        $this->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new FrontResourceImport($this->resource, $this->visibleColumnKeys());
        Excel::import($import, $this->import_file);

        $this->import_summary = [
            'imported' => $import->imported,
            'ignored' => $import->ignored,
            'errors' => $import->errors,
        ];
    }

    public function columnsEnabled(): bool
    {
        return (bool) $this->front()->enable_column_preferences;
    }

    public function sortingEnabled(): bool
    {
        return (bool) $this->front()->enable_index_sorting;
    }

    public function exportEnabled(): bool
    {
        return (bool) $this->front()->enable_export;
    }

    public function importEnabled(): bool
    {
        return (bool) $this->front()->enable_import;
    }

    public function availableColumns()
    {
        $front = $this->front();
        $columns = collect();

        foreach ($front->configurableIndexFields() as $index => $field) {
            $key = $front->indexColumnKey($field, $index);

            $columns->put($key, [
                'key' => $key,
                'title' => $field->title,
            ]);
        }

        return $columns;
    }

    public function visibleColumnKeys(): array
    {
        $available = $this->availableDefaultColumnKeys();
        $preferences = $this->columnPreferences();
        $columns = $preferences['columns'] ?? $available;
        $columns = array_values(array_intersect($columns, $this->availableColumns()->keys()->all()));

        if (count($columns) === 0) {
            return array_slice($available, 0, 1);
        }

        return $columns;
    }

    public function manualColumnKeys(): array
    {
        return $this->columnPreferences()['manual'] ?? [];
    }

    private function indexResponse()
    {
        $front = Front::makeResource($this->resource)->setSource('index');

        $this->syncSortRequest();
        $this->authorizeIndex($front);

        $response = $front->beforeRequest();

        if ($response) {
            return $response;
        }

        return $this->run(new FrontIndex($front, $front->getBaseUrl()));
    }

    private function authorizeIndex($front): void
    {
        $this->authorize('viewAny', $front->getModel());
        $this->frontAuthorize($front, 'index');
    }

    private function syncSortRequest(): void
    {
        request()->merge([
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);
    }

    private function frontAuthorize($front, string $method): void
    {
        if (! in_array($method, $front->actions)) {
            abort(403, 'This action is unauthorized.');
        }
    }

    private function availableDefaultColumnKeys(): array
    {
        $front = $this->front();
        $columns = [];

        foreach ($front->indexFields() as $index => $field) {
            $columns[] = $front->indexColumnKey($field, $index);
        }

        return $columns;
    }

    private function columnPreferences(): array
    {
        return cache()->get($this->columnPreferenceCacheKey(), []);
    }

    private function saveColumnPreferences(array $columns, array $manual): void
    {
        cache()->forever($this->columnPreferenceCacheKey(), [
            'columns' => array_values($columns),
            'manual' => array_values($manual),
        ]);
    }

    private function columnPreferenceCacheKey(): string
    {
        $userKey = auth()->id() ? 'user:'.auth()->id() : 'session:'.session()->getId();

        return 'front:index-columns:'.$userKey.':'.md5($this->resource.':'.$this->front()->getCurrentViewName());
    }

    private function buildImportPreview(): array
    {
        $front = $this->front();
        $fields = $front->configurableIndexFieldsForColumns($this->visibleColumnKeys());
        $importable = $front->importableIndexFields($this->visibleColumnKeys());
        $importableKeys = $importable->pluck('front_column_key')->all();
        $preview = [];

        foreach ($fields as $field) {
            $preview[] = [
                'title' => $field->title,
                'status' => in_array($field->front_column_key, $importableKeys) ? 'importable' : 'ignored',
            ];
        }

        return $preview;
    }

    public function render()
    {
        return view('front::livewire.resource-index');
    }
}
