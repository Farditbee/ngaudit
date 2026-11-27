{{--
    Toolbar editor TipTap universal bergaya MS Word.
    Mendukung:
    1. Jenis Font (Font Family)
    2. Ukuran Font (Default 11pt)
    3. Line Spacing (Spasi Baris)
    4. Multilevel List & Tipe Poin (Angka, Alfabet, Romawi, Bullet)
    5. Sisipkan Tabel bergaya MS Word (Grid Picker Baris x Kolom + Input Manual)
--}}
@php
    $iconButton = 'inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors';
    $idleClass = 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800';
    $activeClass = 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white';
    $tableButton = 'inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium transition-colors';
    $selectClass = 'h-8 rounded-md border border-neutral-200 bg-white px-2 text-xs text-neutral-800 shadow-2xs focus:border-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200';
@endphp

<div
    x-data="{
        tick: 0,
        listMenuOpen: false,
        lineSpacingOpen: false,
        tablePickerOpen: false,
        hoverRows: 0,
        hoverCols: 0,
        customRows: 3,
        customCols: 3,
    }"
    x-on:tiptap-update.window="tick++"
    class="flex flex-wrap items-center gap-1 border-b border-neutral-200 bg-neutral-50/80 p-2 dark:border-neutral-800 dark:bg-neutral-900/60"
>
    {{-- Undo / Redo --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="undo(); tick++" :disabled="(tick, !canUndo())" title="Undo (Ctrl+Z)" class="{{ $iconButton }} {{ $idleClass }} disabled:opacity-40">
            <x-lucide-undo class="h-4 w-4" />
        </button>
        <button type="button" x-on:click="redo(); tick++" :disabled="(tick, !canRedo())" title="Redo (Ctrl+Y)" class="{{ $iconButton }} {{ $idleClass }} disabled:opacity-40">
            <x-lucide-redo class="h-4 w-4" />
        </button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- 1. Jenis Font (Font Family) --}}
    <div class="inline-flex items-center">
        <select
            title="Jenis Font"
            x-bind:value="(tick, currentFontFamily())"
            x-on:change="setFontFamily($event.target.value); tick++"
            class="{{ $selectClass }} w-32 sm:w-36"
        >
            <option value="">Font Default</option>
            <option value="Arial, Helvetica, sans-serif" style="font-family: Arial, Helvetica, sans-serif;">Arial</option>
            <option value="Times New Roman, Times, serif" style="font-family: 'Times New Roman', Times, serif;">Times New Roman</option>
            <option value="Calibri, Carlito, sans-serif" style="font-family: Calibri, Carlito, sans-serif;">Calibri</option>
            <option value="Cambria, Georgia, serif" style="font-family: Cambria, Georgia, serif;">Cambria</option>
            <option value="Tahoma, Geneva, sans-serif" style="font-family: Tahoma, Geneva, sans-serif;">Tahoma</option>
            <option value="Verdana, Geneva, sans-serif" style="font-family: Verdana, Geneva, sans-serif;">Verdana</option>
            <option value="Georgia, serif" style="font-family: Georgia, serif;">Georgia</option>
            <option value="Courier New, Courier, monospace" style="font-family: 'Courier New', Courier, monospace;">Courier New</option>
        </select>
    </div>

    {{-- 2. Ukuran Font (Default 11) --}}
    <div class="inline-flex items-center">
        <select
            title="Ukuran Font (Default 11)"
            x-bind:value="(tick, currentFontSize())"
            x-on:change="setFontSize($event.target.value); tick++"
            class="{{ $selectClass }} w-16"
        >
            @foreach ([8, 9, 10, 11, 12, 14, 16, 18, 20, 24, 28, 36] as $pt)
                <option value="{{ $pt }}pt">{{ $pt }}</option>
            @endforeach
        </select>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Paragraf & Heading --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="setHeading(0); tick++" :class="(tick, isActive('paragraph')) ? '{{ $activeClass }} font-semibold' : '{{ $idleClass }}'" title="Paragraf Normal" class="inline-flex h-8 items-center justify-center rounded-md px-2 text-xs">P</button>
        @foreach ([1, 2, 3] as $level)
            <button type="button" x-on:click="setHeading({{ $level }}); tick++" :class="(tick, isActive('heading', { level: {{ $level }} })) ? '{{ $activeClass }} font-bold' : '{{ $idleClass }}'" title="Heading {{ $level }}" class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold">H{{ $level }}</button>
        @endforeach
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Bold, Italic, Underline, Strike --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="toggleBold(); tick++" :class="(tick, isActive('bold')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Tebal (Ctrl+B)" class="{{ $iconButton }}"><x-lucide-bold class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleItalic(); tick++" :class="(tick, isActive('italic')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Miring (Ctrl+I)" class="{{ $iconButton }}"><x-lucide-italic class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleUnderline(); tick++" :class="(tick, isActive('underline')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Garis Bawah (Ctrl+U)" class="{{ $iconButton }}"><x-lucide-underline class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleStrike(); tick++" :class="(tick, isActive('strike')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Coretan" class="{{ $iconButton }}"><x-lucide-strikethrough class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Superscript / Subscript --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="toggleSuperscript(); tick++" :class="(tick, isActive('superscript')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Pangkat Atas" class="{{ $iconButton }}"><x-lucide-superscript class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleSubscript(); tick++" :class="(tick, isActive('subscript')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Pangkat Bawah" class="{{ $iconButton }}"><x-lucide-subscript class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Alignment & 3. Line Spacing --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="setTextAlign('left'); tick++" :class="(tick, isActive({ textAlign: 'left' })) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Kiri" class="{{ $iconButton }}"><x-lucide-align-left class="h-4 w-4" /></button>
        <button type="button" x-on:click="setTextAlign('center'); tick++" :class="(tick, isActive({ textAlign: 'center' })) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Tengah" class="{{ $iconButton }}"><x-lucide-align-center class="h-4 w-4" /></button>
        <button type="button" x-on:click="setTextAlign('right'); tick++" :class="(tick, isActive({ textAlign: 'right' })) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Kanan" class="{{ $iconButton }}"><x-lucide-align-right class="h-4 w-4" /></button>
        <button type="button" x-on:click="setTextAlign('justify'); tick++" :class="(tick, isActive({ textAlign: 'justify' })) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Kiri-Kanan" class="{{ $iconButton }}"><x-lucide-align-justify class="h-4 w-4" /></button>

        {{-- Dropdown Line Spacing --}}
        <div class="relative" x-on:click.outside="lineSpacingOpen = false">
            <button
                type="button"
                x-on:click="lineSpacingOpen = !lineSpacingOpen; listMenuOpen = false; tablePickerOpen = false"
                title="Line Spacing (Spasi Baris)"
                class="inline-flex h-8 items-center gap-1 rounded-md px-1.5 text-xs font-medium {{ $idleClass }}"
            >
                <x-lucide-arrow-up-down class="h-3.5 w-3.5" />
                <span class="text-[11px]" x-text="(tick, currentLineHeight())"></span>
                <x-lucide-chevron-down class="h-3 w-3 opacity-60" />
            </button>

            <div
                x-show="lineSpacingOpen"
                x-transition
                x-cloak
                class="absolute left-0 top-full z-50 mt-1 w-36 rounded-lg border border-neutral-200 bg-white py-1 shadow-lg dark:border-neutral-700 dark:bg-neutral-900"
            >
                <div class="border-b border-neutral-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:border-neutral-800">
                    Line Spacing
                </div>
                @foreach (['1.0', '1.15', '1.5', '2.0', '2.5', '3.0'] as $lh)
                    <button
                        type="button"
                        x-on:click="setLineHeight('{{ $lh }}'); lineSpacingOpen = false; tick++"
                        class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        :class="(tick, currentLineHeight()) === '{{ $lh }}' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                    >
                        <span>{{ $lh }}</span>
                        <span x-show="(tick, currentLineHeight()) === '{{ $lh }}'" class="text-blue-600 dark:text-blue-400">✓</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- 4. Lists, Multilevel List Selector & Indent --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="toggleBulletList(); tick++" :class="(tick, isActive('bulletList')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Bullet List" class="{{ $iconButton }}"><x-lucide-list class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleOrderedList(); tick++" :class="(tick, isActive('orderedList')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Numbered List" class="{{ $iconButton }}"><x-lucide-list-ordered class="h-4 w-4" /></button>

        {{-- Dropdown Opsi Multilevel List & Tipe Poin (Angka, Alfabet, Romawi, Simbol) --}}
        <div class="relative" x-on:click.outside="listMenuOpen = false">
            <button
                type="button"
                x-on:click="listMenuOpen = !listMenuOpen; lineSpacingOpen = false; tablePickerOpen = false"
                title="Opsi Multilevel List (Angka, Alfabet, Romawi, Bullet)"
                class="inline-flex h-8 items-center gap-1 rounded-md px-2 text-xs font-medium {{ $idleClass }}"
                :class="(tick, isActive('orderedList') || isActive('bulletList')) ? '{{ $activeClass }}' : '{{ $idleClass }}'"
            >
                <x-lucide-list-tree class="h-4 w-4" />
                <x-lucide-chevron-down class="h-3 w-3 opacity-60" />
            </button>

            <div
                x-show="listMenuOpen"
                x-transition
                x-cloak
                class="absolute left-0 top-full z-50 mt-1 w-64 rounded-lg border border-neutral-200 bg-white py-1.5 shadow-xl dark:border-neutral-700 dark:bg-neutral-900"
            >
                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                    Multilevel Otomatis (Tab / Indent)
                </div>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('multilevel'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'multilevel' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-24 font-mono">1. → a. → i.</strong> Multilevel Standar</span>
                </button>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('multilevel-roman'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'multilevel-roman' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-24 font-mono">I. → A. → 1.</strong> Multilevel Romawi</span>
                </button>

                <div class="my-1 border-t border-neutral-100 dark:border-neutral-800"></div>
                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                    Format Penomoran Level Ini
                </div>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('decimal'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'decimal' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16 font-mono">1, 2, 3</strong> Angka</span>
                </button>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('lower-alpha'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'lower-alpha' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16 font-mono">a, b, c</strong> Alfabet Kecil</span>
                </button>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('upper-alpha'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'upper-alpha' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16 font-mono">A, B, C</strong> Alfabet Besar</span>
                </button>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('lower-roman'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'lower-roman' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16 font-mono">i, ii, iii</strong> Romawi Kecil</span>
                </button>
                <button
                    type="button"
                    x-on:click="setOrderedListStyle('upper-roman'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'upper-roman' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16 font-mono">I, II, III</strong> Romawi Besar</span>
                </button>

                <div class="my-1 border-t border-neutral-100 dark:border-neutral-800"></div>
                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                    Format Bullet / Simbol
                </div>
                <button
                    type="button"
                    x-on:click="setBulletListStyle('disc'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'disc' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16">• • •</strong> Bulat Penuh</span>
                </button>
                <button
                    type="button"
                    x-on:click="setBulletListStyle('circle'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'circle' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16">○ ○ ○</strong> Lingkaran</span>
                </button>
                <button
                    type="button"
                    x-on:click="setBulletListStyle('square'); listMenuOpen = false; tick++"
                    class="flex w-full items-center justify-between px-3 py-1.5 text-left text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    :class="(tick, currentListStyle()) === 'square' ? 'bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : 'text-neutral-700 dark:text-neutral-200'"
                >
                    <span><strong class="inline-block w-16">▪ ▪ ▪</strong> Kotak</span>
                </button>
            </div>
        </div>

        <button type="button" x-on:click="liftListItem(); tick++" title="Kurangi Indent Level (Shift+Tab)" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-outdent class="h-4 w-4" /></button>
        <button type="button" x-on:click="sinkListItem(); tick++" title="Tambah Indent Sub-Level (Tab)" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-indent class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- 5. Tabel Bergaya MS Word (Grid Picker Baris x Kolom + Input Manual) --}}
    <div class="inline-flex items-center gap-0.5">
        <div class="relative" x-on:click.outside="tablePickerOpen = false">
            <button
                type="button"
                x-on:click="tablePickerOpen = !tablePickerOpen; hoverRows = 0; hoverCols = 0; listMenuOpen = false; lineSpacingOpen = false"
                title="Sisipkan Tabel (Pilih Baris & Kolom)"
                class="inline-flex h-8 items-center gap-1 rounded-md px-2 text-xs font-medium {{ $idleClass }}"
                :class="(tick, isActive('table')) ? '{{ $activeClass }}' : '{{ $idleClass }}'"
            >
                <x-lucide-table class="h-4 w-4" />
                <x-lucide-chevron-down class="h-3 w-3 opacity-60" />
            </button>

            <div
                x-show="tablePickerOpen"
                x-transition
                x-cloak
                class="absolute left-0 top-full z-50 mt-1 w-60 rounded-lg border border-neutral-200 bg-white p-3 shadow-xl dark:border-neutral-700 dark:bg-neutral-900"
            >
                <div class="mb-2 flex items-center justify-between text-xs font-semibold text-neutral-700 dark:text-neutral-200">
                    <span x-text="hoverRows > 0 && hoverCols > 0 ? 'Tabel ' + hoverRows + ' Baris × ' + hoverCols + ' Kolom' : 'Sisipkan Tabel'"></span>
                    <span
                        x-show="hoverRows > 0 && hoverCols > 0"
                        class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-600 dark:bg-blue-950/60 dark:text-blue-400"
                        x-text="hoverCols + ' × ' + hoverRows"
                    ></span>
                </div>

                {{-- Grid Picker 8x8 ala MS Word --}}
                <div
                    class="grid grid-cols-8 gap-1 p-1"
                    x-on:mouseleave="hoverRows = 0; hoverCols = 0"
                >
                    @for ($r = 1; $r <= 8; $r++)
                        @for ($c = 1; $c <= 8; $c++)
                            <button
                                type="button"
                                x-on:mouseenter="hoverRows = {{ $r }}; hoverCols = {{ $c }}"
                                x-on:click="insertTable({{ $r }}, {{ $c }}); tablePickerOpen = false; tick++"
                                class="h-5 w-5 rounded-xs border transition-colors"
                                :class="({{ $r }} <= hoverRows && {{ $c }} <= hoverCols)
                                    ? 'border-blue-500 bg-blue-500/25 dark:border-blue-400 dark:bg-blue-500/40'
                                    : 'border-neutral-300 bg-neutral-50 hover:border-blue-400 dark:border-neutral-700 dark:bg-neutral-800'"
                                title="{{ $r }} Baris × {{ $c }} Kolom"
                            ></button>
                        @endfor
                    @endfor
                </div>

                {{-- Input Manual Baris & Kolom --}}
                <div class="mt-2.5 border-t border-neutral-200 pt-2.5 dark:border-neutral-800">
                    <div class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                        Ukuran Kustom
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="flex-1">
                            <label class="block text-[10px] text-neutral-500">Baris</label>
                            <input
                                type="number"
                                min="1"
                                max="50"
                                x-model.number="customRows"
                                class="h-7 w-full rounded border border-neutral-200 bg-white px-2 text-xs text-neutral-800 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
                            />
                        </div>
                        <span class="mt-3 text-xs text-neutral-400">×</span>
                        <div class="flex-1">
                            <label class="block text-[10px] text-neutral-500">Kolom</label>
                            <input
                                type="number"
                                min="1"
                                max="20"
                                x-model.number="customCols"
                                class="h-7 w-full rounded border border-neutral-200 bg-white px-2 text-xs text-neutral-800 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100"
                            />
                        </div>
                        <button
                            type="button"
                            x-on:click="insertTable(customRows, customCols); tablePickerOpen = false; tick++"
                            class="mt-3.5 inline-flex h-7 items-center justify-center rounded bg-blue-600 px-2.5 text-[11px] font-semibold text-white hover:bg-blue-700"
                        >
                            Buat
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <template x-if="(tick, isActive('table'))">
            <div class="inline-flex items-center gap-0.5">
                <button type="button" x-on:click="addRowAfter(); tick++" title="Tambah Baris Bawah" class="{{ $tableButton }} {{ $idleClass }}">+Baris</button>
                <button type="button" x-on:click="addColumnAfter(); tick++" title="Tambah Kolom Kanan" class="{{ $tableButton }} {{ $idleClass }}">+Kolom</button>
                <button type="button" x-on:click="deleteRow(); tick++" title="Hapus Baris" class="{{ $tableButton }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">-Baris</button>
                <button type="button" x-on:click="deleteColumn(); tick++" title="Hapus Kolom" class="{{ $tableButton }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">-Kolom</button>
                <button type="button" x-on:click="deleteTable(); tick++" title="Hapus Tabel" class="{{ $tableButton }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">Hapus Tabel</button>
            </div>
        </template>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Link, Gambar, Bersihkan Format --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="setLink(); tick++" :class="(tick, isActive('link')) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Sisipkan Link" class="{{ $iconButton }}"><x-lucide-link class="h-4 w-4" /></button>
        <button type="button" x-on:click="insertImage(); tick++" title="Sisipkan Gambar / Embed" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-image class="h-4 w-4" /></button>
        <button type="button" x-on:click="clearFormatting(); tick++" title="Bersihkan Format" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-remove-formatting class="h-4 w-4" /></button>
    </div>
</div>
