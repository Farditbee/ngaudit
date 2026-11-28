@props([
    'label' => null,
    'placeholder' => 'Semua',
    'itemsExpr',
    'valueModel',
    'changeAction' => '',
    'disabledExpr' => 'false',
    'disabledMessage' => '',
    'wrap' => false,
    'allowEmpty' => true,
])

<div
    class="relative space-y-1.5"
    x-data="{
        open: false,
        dropUp: false,
        query: '',
        parentHint: false,
        highlightedIndex: -1,
        get isDisabled() {
            return Boolean({{ $disabledExpr }});
        },
        get items() {
            return {{ $itemsExpr }} || [];
        },
        get filteredItems() {
            const q = this.query.trim().toLowerCase();
            let list = this.items;
            if (q) {
                list = list.filter(item =>
                    item.name.toLowerCase().includes(q) ||
                    (item.group && item.group.toLowerCase().includes(q))
                );
            }
            if (list.some(item => item.group)) {
                const groups = new Map();
                for (const item of list) {
                    const key = item.group || '';
                    if (!groups.has(key)) {
                        groups.set(key, []);
                    }
                    groups.get(key).push(item);
                }
                const groupedList = [];
                for (const itemsOfGroup of groups.values()) {
                    groupedList.push(...itemsOfGroup);
                }
                return groupedList;
            }
            return list;
        },
        get selectedLabel() {
            const val = {{ $valueModel }};
            if (!val) return '{{ $placeholder }}';
            const found = this.items.find(item => String(item.code) === String(val));
            return found ? found.name : '{{ $placeholder }}';
        },
        updatePlacement() {
            const trigger = this.$refs.triggerButton;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const scrollContainer = trigger.closest('.overflow-y-auto');
            const containerRect = scrollContainer
                ? scrollContainer.getBoundingClientRect()
                : { top: 0, bottom: window.innerHeight };
            const spaceBelow = containerRect.bottom - rect.bottom;
            const spaceAbove = rect.top - containerRect.top;
            const estimatedDropdownHeight = 280;
            this.dropUp = spaceBelow < estimatedDropdownHeight && spaceAbove > spaceBelow;
        },
        toggle() {
            if (this.isDisabled) {
                this.open = false;
                this.parentHint = true;
                return;
            }
            this.parentHint = false;
            if (!this.open) {
                this.updatePlacement();
            }
            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.highlightedIndex = this.filteredItems.length > 0 ? 0 : -1;
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                    this.scrollToHighlighted();
                });
            }
        },
        moveDown() {
            const max = this.filteredItems.length - 1;
            if (max < 0) {
                this.highlightedIndex = -1;
                return;
            }
            this.highlightedIndex = this.highlightedIndex < max ? this.highlightedIndex + 1 : -1;
            this.scrollToHighlighted();
        },
        moveUp() {
            const max = this.filteredItems.length - 1;
            if (max < 0) {
                this.highlightedIndex = -1;
                return;
            }
            this.highlightedIndex = this.highlightedIndex > -1 ? this.highlightedIndex - 1 : max;
            this.scrollToHighlighted();
        },
        selectHighlighted() {
            if (this.highlightedIndex === -1) {
                if (this.filteredItems.length === 1) {
                    this.choose(this.filteredItems[0].code);
                } else {
                    this.choose('');
                }
                return;
            }
            const item = this.filteredItems[this.highlightedIndex];
            if (item) {
                this.choose(item.code);
            }
        },
        scrollToHighlighted() {
            this.$nextTick(() => {
                const list = this.$refs.optionsList;
                if (!list) return;
                const el = list.querySelector(`[data-option-index='${this.highlightedIndex}']`);
                if (el) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
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
    @if ($label)
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">{{ $label }}</label>
    @endif

    <button
        x-ref="triggerButton"
        type="button"
        x-on:click="toggle()"
        x-on:keydown.arrow-down.prevent="if (!open) toggle(); else moveDown()"
        x-on:keydown.arrow-up.prevent="if (!open) toggle(); else moveUp()"
        class="flex {{ $wrap ? 'min-h-9 py-1.5' : 'h-9 py-1' }} w-full items-center justify-between gap-2 rounded-md border border-neutral-200 bg-white px-3 text-left text-sm text-neutral-900 shadow-xs transition-colors hover:bg-neutral-50 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:hover:bg-neutral-900 dark:focus:border-neutral-100 dark:focus:ring-white/20"
    >
        <span class="{{ $wrap ? 'break-words leading-snug' : 'truncate' }}" x-bind:class="!{{ $valueModel }} ? 'text-neutral-500 dark:text-neutral-400' : ''" x-text="selectedLabel"></span>
        <x-lucide-chevrons-up-down class="h-3.5 w-3.5 shrink-0 text-neutral-400" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        x-bind:class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
        class="absolute left-0 right-0 z-40 overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="relative border-b border-neutral-200 p-1.5 dark:border-neutral-800">
            <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
            <input
                x-ref="searchInput"
                type="search"
                x-model="query"
                x-on:input="highlightedIndex = filteredItems.length > 0 ? 0 : -1; scrollToHighlighted()"
                x-on:keydown.arrow-down.prevent="moveDown()"
                x-on:keydown.arrow-up.prevent="moveUp()"
                x-on:keydown.enter.prevent="selectHighlighted()"
                placeholder="Cari {{ strtolower($label) }}..."
                class="block h-8 w-full rounded-md border border-neutral-200 bg-neutral-50/60 pl-8 pr-2.5 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:bg-neutral-950"
            />
        </div>

        <ul x-ref="optionsList" class="max-h-56 space-y-0.5 overflow-y-auto p-1 text-sm">
            @if ($allowEmpty)
            <li>
                <button
                    type="button"
                    data-option-index="-1"
                    x-on:click="choose('')"
                    x-on:mouseenter="highlightedIndex = -1"
                    class="flex w-full items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-left text-sm transition-colors"
                    x-bind:class="[
                        {{ $valueModel }} === ''
                            ? 'bg-emerald-50 font-medium text-emerald-800 hover:bg-emerald-100/80 dark:bg-emerald-950/50 dark:text-emerald-300 dark:hover:bg-emerald-900/50'
                            : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800',
                        highlightedIndex === -1 && {{ $valueModel }} !== '' ? 'bg-neutral-100 dark:bg-neutral-800' : '',
                        highlightedIndex === -1 && {{ $valueModel }} === '' ? 'ring-1 ring-emerald-300 dark:ring-emerald-700' : ''
                    ]"
                >
                    <span class="truncate">{{ $placeholder }}</span>
                    <x-lucide-check x-show="{{ $valueModel }} === ''" class="h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                </button>
            </li>
            @endif

            <template x-for="(item, idx) in filteredItems" :key="item.code">
                <li>
                    <template x-if="item.group && (idx === 0 || filteredItems[idx - 1]?.group !== item.group)">
                        <div
                            class="px-2.5 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 select-none dark:text-neutral-500"
                            :class="idx > 0 ? 'mt-1 border-t border-neutral-100 dark:border-neutral-800/80' : ''"
                            x-text="item.group"
                        ></div>
                    </template>
                    <button
                        type="button"
                        x-bind:data-option-index="idx"
                        x-on:click="choose(item.code)"
                        x-on:mouseenter="highlightedIndex = idx"
                        class="flex w-full items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-left text-sm transition-colors"
                        x-bind:class="[
                            String({{ $valueModel }}) === String(item.code)
                                ? 'bg-emerald-50 font-medium text-emerald-800 hover:bg-emerald-100/80 dark:bg-emerald-950/50 dark:text-emerald-300 dark:hover:bg-emerald-900/50'
                                : 'text-neutral-700 hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-neutral-800',
                            highlightedIndex === idx && String({{ $valueModel }}) !== String(item.code) ? 'bg-neutral-100 dark:bg-neutral-800' : '',
                            highlightedIndex === idx && String({{ $valueModel }}) === String(item.code) ? 'ring-1 ring-emerald-300 dark:ring-emerald-700' : ''
                        ]"
                    >
                        <span class="{{ $wrap ? 'break-words leading-snug' : 'truncate' }}" x-text="item.name"></span>
                        <x-lucide-check x-show="String({{ $valueModel }}) === String(item.code)" x-cloak class="h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
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
