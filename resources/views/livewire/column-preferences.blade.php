@if($this->columnsEnabled())
    <div
        x-data="{
            open: false,
            applying: false,
            dragging: null,
            serverColumns: @js($this->columnsForPreferences()),
            serverVisible: @js($this->visibleColumnKeys()),
            serverManual: @js($this->manualColumnKeys()),
            defaultVisible: @js($this->defaultColumnKeysForPreferences()),
            columns: [],
            visible: [],
            manual: [],
            resetDraft() {
                this.columns = [...this.serverColumns];
                this.visible = [...this.serverVisible];
                this.manual = [...this.serverManual];
            },
            resetToDefault() {
                this.visible = this.defaultVisible.filter((key) => this.columns.some((column) => column.key === key));
                this.manual = [];
            },
            orderedColumns() {
                return [
                    ...this.visible.map((key) => this.columns.find((column) => column.key === key)).filter(Boolean),
                    ...this.columns.filter((column) => !this.visible.includes(column.key)),
                ];
            },
            isVisible(key) {
                return this.visible.includes(key);
            },
            toggle(key) {
                if (this.isVisible(key)) {
                    if (this.visible.length <= 1) return;
                    this.visible = this.visible.filter((visibleKey) => visibleKey !== key);
                } else {
                    this.visible = [...this.visible, key];
                }

                if (!this.manual.includes(key)) {
                    this.manual = [...this.manual, key];
                }
            },
            move(key, targetKey) {
                if (!this.isVisible(key) || !this.isVisible(targetKey) || key === targetKey) return;

                const nextVisible = this.visible.filter((visibleKey) => visibleKey !== key);
                const targetIndex = nextVisible.indexOf(targetKey);
                nextVisible.splice(targetIndex, 0, key);
                this.visible = nextVisible;
            },
            async apply() {
                if (this.visible.length === 0 || this.applying) return;

                this.applying = true;

                try {
                    await this.$wire.applyColumnPreferences(this.visible, this.manual);
                    this.serverVisible = [...this.visible];
                    this.serverManual = [...this.manual];
                    this.open = false;
                } finally {
                    this.applying = false;
                }
            },
        }"
        x-on:front-columns-open.window="resetDraft(); open = true"
        x-on:keydown.escape.window="if (!applying) open = false"
        x-show="open"
        x-cloak
        class="relative z-50"
        aria-labelledby="front-columns-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-secondary-950/30 backdrop-blur-sm" x-on:click="if (!applying) open = false" x-transition.opacity></div>

        <div class="fixed inset-0 overflow-y-auto p-4 sm:p-6">
            <div class="mx-auto flex min-h-full max-w-2xl items-center justify-center">
                <form x-on:submit.prevent="apply" class="w-full rounded-2xl bg-white shadow-xl ring-1 ring-secondary-900/10" x-transition>
                    <div class="border-b border-secondary-100 px-5 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 id="front-columns-title" class="text-base font-semibold text-secondary-950">{{ __('Visible columns') }}</h3>
                                <p class="mt-1 text-sm text-secondary-500">{{ __('Choose, reorder, and apply the columns shown in the table.') }}</p>
                            </div>
                            <button type="button" x-on:click="open = false" x-bind:disabled="applying" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-secondary-400 hover:bg-secondary-100 hover:text-secondary-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <x-icon name="x-mark" class="h-4 w-4" />
                                <span class="sr-only">{{ __('Cancel') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="max-h-[65vh] overflow-y-auto px-5 py-4 sm:px-6">
                        <div class="space-y-2">
                            <template x-for="column in orderedColumns()" :key="column.key">
                                <div
                                    draggable="true"
                                    x-on:dragstart="dragging = column.key"
                                    x-on:dragover.prevent
                                    x-on:drop.prevent="move(dragging, column.key); dragging = null"
                                    x-bind:class="isVisible(column.key) ? 'border-primary-100 bg-primary-50/40' : 'border-secondary-100 bg-white opacity-75'"
                                    class="flex items-center gap-3 rounded-lg border px-3 py-2 text-sm transition"
                                >
                                    <button type="button" class="cursor-grab text-secondary-300 hover:text-secondary-500 active:cursor-grabbing" x-bind:class="{ 'opacity-30': !isVisible(column.key) }" aria-label="{{ __('Drag to reorder') }}">
                                        <x-icon name="bars-3" class="h-4 w-4" />
                                    </button>

                                    <button type="button" x-on:click="toggle(column.key)" x-bind:aria-checked="isVisible(column.key)" x-bind:aria-label="column.title" role="checkbox" class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition hover:border-primary-500" x-bind:class="isVisible(column.key) ? 'border-primary-600 bg-primary-600 text-white' : 'border-secondary-300 bg-white text-transparent'">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                        <span class="sr-only" x-text="isVisible(column.key) ? '{{ __('Active') }}' : '{{ __('Hidden') }}'"></span>
                                    </button>

                                    <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-3" x-on:click="toggle(column.key)">
                                        <span class="truncate font-medium text-secondary-800" x-text="column.title"></span>
                                    </label>

                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" x-bind:class="isVisible(column.key) ? 'bg-primary-100 text-primary-700' : 'bg-secondary-100 text-secondary-500'" x-text="isVisible(column.key) ? '{{ __('Active') }}' : '{{ __('Hidden') }}'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-secondary-100 px-5 py-4 sm:flex-row sm:justify-between sm:px-6">
                        <button type="button" x-on:click="resetToDefault" x-bind:disabled="applying" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold text-secondary-500 hover:bg-secondary-100 hover:text-secondary-800 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ __('Reset') }}
                        </button>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" x-on:click="open = false" x-bind:disabled="applying" class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold text-secondary-600 hover:bg-secondary-100 disabled:cursor-not-allowed disabled:opacity-50">
                                {{ __('Cancel') }}
                            </button>
                            <div class="flex flex-col items-stretch gap-1 sm:items-end">
                                <button type="submit" x-bind:disabled="visible.length === 0 || applying" class="inline-flex items-center justify-center gap-2 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50">
                                    <svg x-show="applying" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    {{ __('Apply columns') }}
                                </button>
                                <p x-show="visible.length === 0" x-cloak class="text-xs font-medium text-negative-600">{{ __('Select at least one column.') }}</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
