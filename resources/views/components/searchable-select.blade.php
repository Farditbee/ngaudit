@props([
    'label',
    'placeholder' => 'Semua',
    'itemsExpr',
    'valueModel',
    'changeAction',
    'disabledExpr' => 'false',
    'disabledMessage' => '',
])

<div
    class="relative space-y-1.5"
    x-data="{
        open: false,
        query: '',
        parentHint: false,
        get isDisabled() {
            return Boolean({{ $disabledExpr }});
        },
        get items() {
            return {{ $itemsExpr }} || [];
        },
        get filteredItems() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.items;
            return this.items.filter(item => item.name.toLowerCase().includes(q));
        },
        get selectedLabel() {
            const val = {{ $valueModel }};
            if (!val) return '{{ $placeholder }}';
            const found = this.items.find(item => String(item.code) === String(val));
            return found ? found.name : '{{ $placeholder }}';
        },
        toggle() {
            if (this.isDisabled) {
                this.open = false;
                this.parentHint = true;
                return;
            }
            this.parentHint = false;
            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.$nextTick(() => this.$refs.searchInput?.focus());
            }
        },
        choose(code) {
            {{ $valueModel }} = code;
            this.open = false;
            this.query = '';
            this.parentHint = false;
            {{ $changeAction }};
        }
    }"
    x-effect="if (!isDisabled) parentHint = false"
    x-on:click.outside="open = false; parentHint = false"
    x-on:keydown.escape.window="open = false; parentHint = false"
>
    <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">{{ $label }}</label>

    <button
        type="button"
        x-on:click="toggle()"
        class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-neutral-200 bg-white px-3 py-1 text-left text-sm text-neutral-900 shadow-xs transition-colors hover:bg-neutral-50 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:hover:bg-neutral-900 dark:focus:border-neutral-100 dark:focus:ring-white/20"
    >
        <span class="truncate" x-bind:class="!{{ $valueModel }} ? 'text-neutral-500 dark:text-neutral-400' : ''" x-text="selectedLabel"></span>
        <x-lucide-chevrons-up-down class="h-3.5 w-3.5 shrink-0 text-neutral-400" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        class="absolute left-0 right-0 z-30 mt-1 overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="relative border-b border-neutral-200 p-1.5 dark:border-neutral-800">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
            <input
                x-ref="searchInput"
                type="search"
                x-model="query"
                placeholder="Cari {{ strtolower($label) }}..."
                class="block h-8 w-full rounded-md border border-neutral-200 bg-neutral-50/60 pl-8 pr-2.5 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:bg-neutral-950"
            />
        </div>

        <ul class="max-h-56 overflow-y-auto p-1 text-sm">
            <li>
                <button
                    type="button"
                    x-on:click="choose('')"
                    class="flex w-full items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-left text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    x-bind:class="{{ $valueModel }} === '' ? 'bg-neutral-100 font-medium dark:bg-neutral-800' : 'text-neutral-600 dark:text-neutral-400'"
                >
                    <span class="truncate">{{ $placeholder }}</span>
                    <x-lucide-check x-show="{{ $valueModel }} === ''" class="h-3.5 w-3.5 shrink-0" />
                </button>
            </li>

            <template x-for="item in filteredItems" :key="item.code">
                <li>
                    <button
                        type="button"
                        x-on:click="choose(item.code)"
                        class="flex w-full items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-left text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        x-bind:class="String({{ $valueModel }}) === String(item.code) ? 'bg-neutral-100 font-medium dark:bg-neutral-800' : ''"
                    >
                        <span class="truncate" x-text="item.name"></span>
                        <x-lucide-check x-show="String({{ $valueModel }}) === String(item.code)" x-cloak class="h-3.5 w-3.5 shrink-0" />
                    </button>
                </li>
            </template>

            <template x-if="filteredItems.length === 0">
                <li class="px-2.5 py-3 text-center text-xs text-neutral-500 dark:text-neutral-400">
                    Tidak ditemukan.
                </li>
            </template>
        </ul>
    </div>

    @if ($disabledMessage !== '')
        <p
            x-show="parentHint && isDisabled"
            x-cloak
            x-transition.opacity
            class="flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400"
        >
            <x-lucide-info class="h-3.5 w-3.5 shrink-0" />
            <span>{{ $disabledMessage }}</span>
        </p>
    @endif
</div>
