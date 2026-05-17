@if($this->exportEnabled() || $this->importEnabled())
    <div class="mt-4 flex flex-wrap items-start gap-2">
        @if($this->exportEnabled())
            <button type="button" wire:click="export" class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                {{ __('Export') }}
            </button>
        @endif

        @if($this->importEnabled())
            <button type="button" wire:click="$toggle('show_import')" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                {{ __('Import') }}
            </button>
        @endif
    </div>

    @if($this->show_import && $this->importEnabled())
        <form wire:submit="runImport" class="mt-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input :label="__('Excel file')" type="file" wire:model="import_file" />
                    @error('import_file')
                        <p class="mt-1 text-sm text-negative-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ __('Import preview') }}</p>
                    <div class="mt-2 space-y-1 text-sm text-gray-600">
                        @forelse($this->import_preview as $field)
                            <div class="flex items-center justify-between gap-3 rounded border border-gray-100 px-2 py-1">
                                <span>{{ $field['title'] }}</span>
                                <span @class([
                                    'text-xs font-semibold',
                                    'text-emerald-600' => $field['status'] === 'importable',
                                    'text-gray-400' => $field['status'] !== 'importable',
                                ])>
                                    {{ $field['status'] === 'importable' ? __('Will import') : __('Ignored') }}
                                </span>
                            </div>
                        @empty
                            <p>{{ __('Select a file to preview importable columns.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    {{ __('Run import') }}
                </button>
                <span wire:loading wire:target="import_file,runImport" class="text-sm text-gray-500">{{ __('Processing...') }}</span>
            </div>

            @if($this->import_summary)
                <div class="mt-4 rounded-md bg-gray-50 p-3 text-sm text-gray-700">
                    <p>{{ __('Imported') }}: {{ $this->import_summary['imported'] }}</p>
                    <p>{{ __('Ignored') }}: {{ $this->import_summary['ignored'] }}</p>
                    @foreach($this->import_summary['errors'] as $error)
                        <p class="text-negative-600">{{ __('Row') }} {{ $error['row'] }}: {{ $error['message'] }}</p>
                    @endforeach
                </div>
            @endif
        </form>
    @endif
@endif
