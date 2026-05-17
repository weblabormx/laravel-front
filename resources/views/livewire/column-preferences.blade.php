@if($this->columnsEnabled())
    <div class="mt-4">
        <button type="button" wire:click="$toggle('show_columns')" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            <span>{{ __('Columns') }}</span>
            <span class="text-xs text-gray-400">{{ $this->show_columns ? '↑' : '↓' }}</span>
        </button>

        @if($this->show_columns)
            <div class="mt-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-gray-900">{{ __('Visible columns') }}</p>
                    <button type="button" wire:click="resetColumns" class="text-sm text-primary-600 hover:text-primary-700">
                        {{ __('Reset') }}
                    </button>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($this->availableColumns() as $column)
                        @php $visible = in_array($column['key'], $this->visibleColumnKeys()); @endphp
                        <div class="flex items-center justify-between gap-2 rounded-md border border-gray-100 px-3 py-2">
                            <x-checkbox :label="$column['title']" wire:click="toggleColumn('{{ $column['key'] }}')" :checked="$visible" />

                            @if($visible)
                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" wire:click="moveColumn('{{ $column['key'] }}', 'up')" class="text-xs text-gray-400 hover:text-gray-700">↑</button>
                                    <button type="button" wire:click="moveColumn('{{ $column['key'] }}', 'down')" class="text-xs text-gray-400 hover:text-gray-700">↓</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
