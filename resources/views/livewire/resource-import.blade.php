@php
    $front = $this->front();
@endphp

<section aria-label="{{ __('Import :resource', ['resource' => $front->plural_label]) }}" class="space-y-6">
    @include('front::elements.breadcrumbs')

    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-sm font-medium text-secondary-500">{{ $front->plural_label }}</p>
            <h2 class="text-2xl font-bold leading-7 text-secondary-950 sm:text-3xl sm:tracking-tight">
                {{ __('Import :resource', ['resource' => $front->plural_label]) }}
            </h2>
            <p class="mt-2 max-w-2xl text-sm text-secondary-500">
                {{ __('Upload an Excel or CSV file, validate its structure, then run the import.') }}
            </p>
        </div>

        <a href="{{ $front->getBaseUrl() }}" class="inline-flex items-center gap-2 rounded-md border border-secondary-300 bg-white px-3 py-2 text-sm font-medium text-secondary-700 shadow-sm hover:bg-secondary-50">
            <x-icon name="arrow-left" class="h-4 w-4 text-secondary-400" />
            {{ __('Back to list') }}
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-secondary-200 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-50 text-sm font-semibold text-primary-700">1</span>
                <div>
                    <h3 class="text-sm font-semibold text-secondary-950">{{ __('Upload file') }}</h3>
                    <p class="mt-1 text-sm text-secondary-500">{{ __('Supported formats: XLSX, XLS and CSV.') }}</p>
                </div>
            </div>
        </div>

        <div @class([
            'rounded-xl border p-5 shadow-sm',
            'border-primary-200 bg-primary-50' => $this->analyzed,
            'border-secondary-200 bg-white' => ! $this->analyzed,
        ])>
            <div class="flex items-start gap-3">
                <span @class([
                    'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                    'bg-primary-600 text-white' => $this->analyzed,
                    'bg-secondary-100 text-secondary-600' => ! $this->analyzed,
                ])>2</span>
                <div>
                    <h3 class="text-sm font-semibold text-secondary-950">{{ __('Analyze structure') }}</h3>
                    <p class="mt-1 text-sm text-secondary-500">{{ __('Review which fields will be imported and which will be ignored.') }}</p>
                </div>
            </div>
        </div>

        <div @class([
            'rounded-xl border p-5 shadow-sm',
            'border-primary-200 bg-primary-50' => $this->import_summary,
            'border-secondary-200 bg-white' => ! $this->import_summary,
        ])>
            <div class="flex items-start gap-3">
                <span @class([
                    'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                    'bg-primary-600 text-white' => $this->import_summary,
                    'bg-secondary-100 text-secondary-600' => ! $this->import_summary,
                ])>3</span>
                <div>
                    <h3 class="text-sm font-semibold text-secondary-950">{{ __('Import rows') }}</h3>
                    <p class="mt-1 text-sm text-secondary-500">{{ __('Run the import and review the final result.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit="analyzeImport" class="rounded-xl border border-secondary-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 lg:grid-cols-2">
            <div x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-cancel="uploading = false" x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress">
                <x-input :label="__('Excel file')" type="file" wire:model="import_file" />
                @error('import_file')
                    <p class="mt-2 text-sm text-negative-600">{{ $message }}</p>
                @enderror

                <div x-show="uploading" x-cloak class="mt-4">
                    <div class="flex items-center justify-between text-xs font-medium text-secondary-500">
                        <span>{{ __('Uploading file') }}</span>
                        <span x-text="progress + '%'"></span>
                    </div>
                    <progress max="100" x-bind:value="progress" class="mt-2 h-2 w-full overflow-hidden rounded-full accent-primary-600"></progress>
                </div>
            </div>

            <div class="rounded-lg bg-secondary-50 p-4 text-sm text-secondary-600">
                <p class="font-semibold text-secondary-900">{{ __('Before importing') }}</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>{{ __('Calculated fields are ignored during import.') }}</li>
                    <li>{{ __('Column names are matched using the exported headings.') }}</li>
                    <li>{{ __('Existing create validations still apply to each row.') }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                <x-icon name="document-magnifying-glass" class="h-4 w-4" />
                {{ __('Analyze file') }}
            </button>
            <button type="button" wire:click="runImport" @disabled(! $this->canImport()) class="inline-flex items-center gap-2 rounded-md bg-secondary-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary-800 disabled:cursor-not-allowed disabled:opacity-50">
                <x-icon name="cloud-arrow-up" class="h-4 w-4" />
                {{ __('Import rows') }}
            </button>
            <span wire:loading wire:target="analyzeImport,runImport" class="text-sm text-secondary-500">{{ __('Processing file...') }}</span>
        </div>

        <div wire:loading wire:target="runImport" class="mt-5">
            <div class="flex items-center justify-between text-xs font-medium text-secondary-500">
                <span>{{ __('Importing rows') }}</span>
                <span>{{ __('Please wait') }}</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-secondary-100">
                <div class="h-2 w-1/2 animate-pulse rounded-full bg-primary-600"></div>
            </div>
        </div>
    </form>

    @if($this->analyzed)
        <div class="rounded-xl border border-secondary-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-secondary-950">{{ __('Import analysis') }}</h3>
                <span @class([
                    'rounded-full px-3 py-1 text-xs font-semibold',
                    'bg-primary-50 text-primary-700' => $this->canImport(),
                    'bg-negative-50 text-negative-700' => ! $this->canImport(),
                ])>{{ $this->canImport() ? __('Ready to import') : __('Needs attention') }}</span>
            </div>

            @if(count($this->import_structure_errors) > 0)
                <div class="mb-4 space-y-2">
                    @foreach($this->import_structure_errors as $error)
                        <p class="rounded-md bg-negative-50 px-3 py-2 text-sm text-negative-700">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->import_preview as $fieldIndex => $field)
                    <div wire:key="import-preview-{{ $fieldIndex }}" class="flex items-center justify-between gap-3 rounded-md border border-secondary-100 px-3 py-2 text-sm">
                        <span class="text-secondary-700">{{ $field['title'] }}</span>
                        <span @class([
                            'text-xs font-semibold',
                            'text-emerald-600' => $field['status'] === 'importable',
                            'text-negative-600' => $field['status'] === 'missing',
                            'text-secondary-400' => $field['status'] === 'ignored',
                        ])>
                            @if($field['status'] === 'importable')
                                {{ __('Will import') }}
                            @elseif($field['status'] === 'missing')
                                {{ __('Missing from file') }}
                            @else
                                {{ __('Ignored') }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($this->import_summary)
        <div class="rounded-xl border border-secondary-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-secondary-950">{{ __('Import result') }}</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg bg-primary-50 p-4">
                    <p class="text-xs font-medium text-primary-700">{{ __('Imported') }}</p>
                    <p class="mt-1 text-2xl font-bold text-primary-900">{{ $this->import_summary['imported'] }}</p>
                </div>
                <div class="rounded-lg bg-secondary-50 p-4">
                    <p class="text-xs font-medium text-secondary-600">{{ __('Ignored') }}</p>
                    <p class="mt-1 text-2xl font-bold text-secondary-900">{{ $this->import_summary['ignored'] }}</p>
                </div>
                <div class="rounded-lg bg-negative-50 p-4">
                    <p class="text-xs font-medium text-negative-700">{{ __('Errors') }}</p>
                    <p class="mt-1 text-2xl font-bold text-negative-900">{{ count($this->import_summary['errors']) }}</p>
                </div>
            </div>

            @if(count($this->import_summary['errors']) > 0)
                <div class="mt-4 space-y-2">
                    @foreach($this->import_summary['errors'] as $error)
                        <p class="rounded-md bg-negative-50 px-3 py-2 text-sm text-negative-700">
                            {{ __('Row') }} {{ $error['row'] }}: {{ $error['message'] }}
                        </p>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</section>
