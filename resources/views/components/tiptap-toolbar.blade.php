{{--
    Toolbar editor TipTap bergaya MS Word.
    Dipakai di dalam scope Alpine yang menyediakan method toolbar TipTap
    (lihat getTipTapToolbarMethods di resources/js/datatables.js).
--}}
@php
    $iconButton = 'inline-flex h-8 w-8 items-center justify-center rounded-md';
    $idleClass = 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800';
    $activeClass = 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white';
    $tableButton = 'inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium';
@endphp

<div class="flex flex-wrap items-center gap-1 border-b border-neutral-200 bg-neutral-50/80 p-2 dark:border-neutral-800 dark:bg-neutral-900/60">
    {{-- Undo / Redo --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="undo()" :disabled="!canUndo()" title="Undo (Ctrl+Z)" class="{{ $iconButton }} {{ $idleClass }} disabled:opacity-40">
            <x-lucide-undo class="h-4 w-4" />
        </button>
        <button type="button" x-on:click="redo()" :disabled="!canRedo()" title="Redo (Ctrl+Y)" class="{{ $iconButton }} {{ $idleClass }} disabled:opacity-40">
            <x-lucide-redo class="h-4 w-4" />
        </button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Paragraf & Heading --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="setHeading(0)" :class="isActive('paragraph') ? '{{ $activeClass }} font-semibold' : '{{ $idleClass }}'" title="Paragraf Normal" class="inline-flex h-8 items-center justify-center rounded-md px-2 text-xs">P</button>
        @foreach ([1, 2, 3] as $level)
            <button type="button" x-on:click="setHeading({{ $level }})" :class="isActive('heading', { level: {{ $level }} }) ? '{{ $activeClass }} font-bold' : '{{ $idleClass }}'" title="Heading {{ $level }}" class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold">H{{ $level }}</button>
        @endforeach
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Bold, Italic, Underline, Strike --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="toggleBold()" :class="isActive('bold') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Tebal (Ctrl+B)" class="{{ $iconButton }}"><x-lucide-bold class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleItalic()" :class="isActive('italic') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Miring (Ctrl+I)" class="{{ $iconButton }}"><x-lucide-italic class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleUnderline()" :class="isActive('underline') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Garis Bawah (Ctrl+U)" class="{{ $iconButton }}"><x-lucide-underline class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleStrike()" :class="isActive('strike') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Coretan" class="{{ $iconButton }}"><x-lucide-strikethrough class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Superscript / Subscript --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="toggleSuperscript()" :class="isActive('superscript') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Pangkat Atas" class="{{ $iconButton }}"><x-lucide-superscript class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleSubscript()" :class="isActive('subscript') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Pangkat Bawah" class="{{ $iconButton }}"><x-lucide-subscript class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Alignment --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="setTextAlign('left')" :class="isActive({ textAlign: 'left' }) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Kiri" class="{{ $iconButton }}"><x-lucide-align-left class="h-4 w-4" /></button>
        <button type="button" x-on:click="setTextAlign('center')" :class="isActive({ textAlign: 'center' }) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Tengah" class="{{ $iconButton }}"><x-lucide-align-center class="h-4 w-4" /></button>
        <button type="button" x-on:click="setTextAlign('right')" :class="isActive({ textAlign: 'right' }) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Kanan" class="{{ $iconButton }}"><x-lucide-align-right class="h-4 w-4" /></button>
        <button type="button" x-on:click="setTextAlign('justify')" :class="isActive({ textAlign: 'justify' }) ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Rata Kiri-Kanan" class="{{ $iconButton }}"><x-lucide-align-justify class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Lists & Indent --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="toggleBulletList()" :class="isActive('bulletList') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Bullet List" class="{{ $iconButton }}"><x-lucide-list class="h-4 w-4" /></button>
        <button type="button" x-on:click="toggleOrderedList()" :class="isActive('orderedList') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Numbered List" class="{{ $iconButton }}"><x-lucide-list-ordered class="h-4 w-4" /></button>
        <button type="button" x-on:click="liftListItem()" title="Kurangi Indent" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-outdent class="h-4 w-4" /></button>
        <button type="button" x-on:click="sinkListItem()" title="Tambah Indent" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-indent class="h-4 w-4" /></button>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Tabel --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="insertTable(3, 3)" title="Sisipkan Tabel (3x3)" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-table class="h-4 w-4" /></button>
        <template x-if="isActive('table')">
            <div class="inline-flex items-center gap-0.5">
                <button type="button" x-on:click="addRowAfter()" title="Tambah Baris Bawah" class="{{ $tableButton }} {{ $idleClass }}">+Baris</button>
                <button type="button" x-on:click="addColumnAfter()" title="Tambah Kolom Kanan" class="{{ $tableButton }} {{ $idleClass }}">+Kolom</button>
                <button type="button" x-on:click="deleteRow()" title="Hapus Baris" class="{{ $tableButton }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">-Baris</button>
                <button type="button" x-on:click="deleteColumn()" title="Hapus Kolom" class="{{ $tableButton }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">-Kolom</button>
                <button type="button" x-on:click="deleteTable()" title="Hapus Tabel" class="{{ $tableButton }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">Hapus Tabel</button>
            </div>
        </template>
    </div>

    <div class="mx-1 h-5 w-px bg-neutral-300 dark:bg-neutral-700"></div>

    {{-- Link, Gambar, Bersihkan Format --}}
    <div class="inline-flex items-center gap-0.5">
        <button type="button" x-on:click="setLink()" :class="isActive('link') ? '{{ $activeClass }}' : '{{ $idleClass }}'" title="Sisipkan Link" class="{{ $iconButton }}"><x-lucide-link class="h-4 w-4" /></button>
        <button type="button" x-on:click="insertImage()" title="Sisipkan Gambar / Embed" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-image class="h-4 w-4" /></button>
        <button type="button" x-on:click="clearFormatting()" title="Bersihkan Format" class="{{ $iconButton }} {{ $idleClass }}"><x-lucide-remove-formatting class="h-4 w-4" /></button>
    </div>
</div>
