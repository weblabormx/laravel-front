@if($this->columnsEnabled() && $this->show_columns)
    <div class="mt-4 rounded-lg border border-secondary-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-secondary-950">{{ __('Visible columns') }}</p>
            <button type="button" wire:click="resetColumns" class="text-sm text-primary-600 hover:text-primary-700">
                {{ __('Reset') }}
            </button>
        </div>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($this->availableColumns() as $column)
                @php $visible = in_array($column['key'], $this->visibleColumnKeys()); @endphp
                <div class="flex items-center justify-between gap-2 rounded-md border border-secondary-100 px-3 py-2">
                    <x-checkbox :label="$column['title']" wire:click="toggleColumn('{{ $column['key'] }}')" :checked="$visible" />

                    @if($visible)
                        <div class="flex shrink-0 items-center gap-1">
                            <button type="button" wire:click="moveColumn('{{ $column['key'] }}', 'up')" class="text-xs text-secondary-400 hover:text-secondary-700">↑</button>
                            <button type="button" wire:click="moveColumn('{{ $column['key'] }}', 'down')" class="text-xs text-secondary-400 hover:text-secondary-700">↓</button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
