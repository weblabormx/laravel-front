<?php

namespace WeblaborMx\Front\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use WeblaborMx\Front\Exports\FrontResourceExport;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Jobs\FrontIndex;
use WeblaborMx\Front\Traits\IsRunable;

class ResourceIndex extends Component
{
    use AuthorizesRequests;
    use IsRunable;

    #[Locked]
    public $resource;
    #[Url(as: 'sort')]
    public $sort = null;
    #[Url(as: 'direction')]
    public $direction;
    public $show_columns = false;
    public $filters = [];

    public function mount(string $resource): void
    {
        $this->resource = $resource;
        $front = $this->front();
        $this->sort = request()->get('sort', $front->defaultIndexSortColumn());
        $this->direction = request()->get('direction', $front->default_sort_direction) === 'asc' ? 'asc' : 'desc';
        $this->filters = $this->initialFilters($front);

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

    public function updatedFilters(): void
    {
        request()->query->remove('page');
    }

    public function sortBy($column): void
    {
        $front = $this->front();

        if (!$front->enable_index_sorting || !$front->sortableIndexFields()->has($column)) {
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

        if (!$front->enable_column_preferences || !$this->availableColumns()->has($column)) {
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
        if (!in_array($direction, ['up', 'down'])) {
            return;
        }

        $front = $this->front();

        if (!$front->enable_column_preferences || !$this->availableColumns()->has($column)) {
            return;
        }

        $columns = $this->visibleColumnKeys();
        $index = array_search($column, $columns);

        if ($index === false) {
            return;
        }

        $newIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (!isset($columns[$newIndex])) {
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

    public function toggleColumns(): void
    {
        $this->show_columns = !$this->show_columns;
    }

    public function applyColumnPreferences(array $columns, array $manual = []): void
    {
        $available = $this->availableColumns()->keys()->all();
        $columns = array_values(array_unique(array_intersect($columns, $available)));
        $manual = array_values(array_unique(array_intersect($manual, $available)));

        if (count($columns) === 0) {
            $columns = array_slice($this->availableDefaultColumnKeys(), 0, 1);
        }

        $this->saveColumnPreferences($columns, $manual);
        $this->show_columns = false;
    }

    public function export()
    {
        $front = $this->front();

        if (!$front->enable_export) {
            abort(403, 'This action is unauthorized.');
        }

        $this->authorizeIndex($front);
        $this->syncSortRequest();

        return Excel::download(
            new FrontResourceExport($front, $this->visibleColumnKeys()),
            str($front->plural_label)->slug('_')->toString().'.xlsx'
        );
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
        $front = $this->front();

        if (!$front->enable_import) {
            return false;
        }

        try {
            $this->authorize('viewAny', $front->getModel());
            $this->frontAuthorize($front, 'index');

            $storeFront = Front::makeResource($this->resource)->setSource('store');
            $this->authorize('create', $storeFront->getModel());
            $this->frontAuthorize($storeFront, 'create');
            $this->frontAuthorize($storeFront, 'store');
        } catch (Throwable $throwable) {
            return false;
        }

        return true;
    }

    public function filterModelKey(string $slug): string
    {
        return trim(str_replace(['[', ']'], ['.', ''], $slug), '.');
    }

    public function activeFiltersCount(): int
    {
        return $this->front()->activeFiltersCount($this->filterRequestValues());
    }

    public function availableColumns()
    {
        $front = $this->front();
        $columns = collect();

        foreach ($front->configurableColumnFields() as $index => $field) {
            $key = $front->indexColumnKey($field, $index);

            $columns->put($key, [
                'key' => $key,
                'title' => $field->title,
            ]);
        }

        return $columns;
    }

    public function columnsForPreferences(): array
    {
        $available = $this->availableColumns();
        $visible = $this->visibleColumnKeys();
        $ordered = [];

        foreach ($visible as $key) {
            if ($available->has($key)) {
                $ordered[] = $available->get($key);
            }
        }

        foreach ($available as $key => $column) {
            if (!in_array($key, $visible)) {
                $ordered[] = $column;
            }
        }

        return $ordered;
    }

    public function defaultColumnKeysForPreferences(): array
    {
        return $this->availableDefaultColumnKeys();
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
        request()->merge($this->filterRequestValues() + [
            'sort' => $this->sort,
            'direction' => $this->direction,
            'dont_redirect' => true,
        ]);
    }

    private function initialFilters($front): array
    {
        $filters = [];

        foreach ($front->getFilters() as $filter) {
            $key = $this->filterModelKey($filter->slug);
            Arr::set($filters, $key, request()->input($filter->slug));
        }

        return $filters;
    }

    private function filterRequestValues(): array
    {
        $values = [];

        foreach ($this->front()->getFilters() as $filter) {
            $values[$filter->slug] = Arr::get($this->filters, $this->filterModelKey($filter->slug));
        }

        return $values;
    }

    private function frontAuthorize($front, string $method): void
    {
        if (!in_array($method, $front->actions)) {
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

    public function render()
    {
        return view('front::livewire.resource-index');
    }
}
