<?php

namespace WeblaborMx\Front\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
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
    public $direction = 'asc';

    public $show_columns = false;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
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

    public function columnsEnabled(): bool
    {
        return (bool) $this->front()->enable_column_preferences;
    }

    public function sortingEnabled(): bool
    {
        return (bool) $this->front()->enable_index_sorting;
    }

    public function availableColumns()
    {
        $front = $this->front();

        return $front->configurableIndexFields()->mapWithKeys(function ($field, $index) use ($front) {
            $key = $front->indexColumnKey($field, $index);

            return [$key => [
                'key' => $key,
                'title' => $field->title,
            ]];
        });
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

        request()->merge([
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);

        $this->authorize('viewAny', $front->getModel());
        $this->frontAuthorize($front, 'index');

        $response = $front->beforeRequest();

        if ($response) {
            return $response;
        }

        return $this->run(new FrontIndex($front, $front->getBaseUrl()));
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

        return $front->indexFields()->map(function ($field, $index) use ($front) {
            return $front->indexColumnKey($field, $index);
        })->values()->all();
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
