@if($this->exportEnabled() || $this->importEnabled() || $this->columnsEnabled())
    <div class="flex items-center gap-2 text-secondary-400">
        @if($this->exportEnabled())
            <button type="button" wire:click="export" x-data x-tooltip.raw="{{ __('Export') }}" aria-label="{{ __('Export') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:bg-secondary-100 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                <x-icon name="arrow-down-tray" class="h-4 w-4" />
            </button>
        @endif

        @if($this->importEnabled())
            <a href="{{ $front->getBaseUrl() }}/import" x-data x-tooltip.raw="{{ __('Import') }}" aria-label="{{ __('Import') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:bg-secondary-100 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
            </a>
        @endif

        @if($this->columnsEnabled())
            <button type="button" x-data x-tooltip.raw="{{ __('Columns') }}" x-on:click="$dispatch('front-columns-open')" aria-label="{{ __('Columns') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-md transition hover:bg-secondary-100 hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                <x-icon name="view-columns" class="h-4 w-4" />
            </button>
        @endif
    </div>
@endif
