@if($this->exportEnabled() || $this->importEnabled() || $this->columnsEnabled())
    <div class="flex items-center gap-1 rounded-lg border border-secondary-200 bg-white/80 p-1 shadow-sm">
        @if($this->exportEnabled())
            <button type="button" wire:click="export" title="{{ __('Export') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-secondary-400 transition hover:bg-secondary-50 hover:text-primary-600">
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                <span class="sr-only">{{ __('Export') }}</span>
            </button>
        @endif

        @if($this->importEnabled())
            <a href="{{ $front->getBaseUrl() }}/import" title="{{ __('Import') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-secondary-400 transition hover:bg-secondary-50 hover:text-primary-600">
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                <span class="sr-only">{{ __('Import') }}</span>
            </a>
        @endif

        @if($this->columnsEnabled())
            <button type="button" wire:click="toggleColumns" title="{{ __('Columns') }}" @class([
                'inline-flex h-9 w-9 items-center justify-center rounded-md transition hover:bg-secondary-50 hover:text-primary-600',
                'bg-primary-50 text-primary-600' => $this->show_columns,
                'text-secondary-400' => ! $this->show_columns,
            ])>
                <x-icon name="view-columns" class="h-4 w-4" />
                <span class="sr-only">{{ __('Columns') }}</span>
            </button>
        @endif
    </div>
@endif
