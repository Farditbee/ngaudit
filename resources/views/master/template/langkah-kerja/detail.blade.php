<x-admin-layout :breadcrumbs="['Data Master', 'Template', 'Langkah Kerja', 'Detail Langkah Kerja']">
    <div class="space-y-6" x-data="templateLangkahKerjaDetailDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('master.template.langkah-kerja.index') }}"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 shadow-2xs transition-colors hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        title="Kembali ke Daftar Template Langkah Kerja"
                    >
                        <x-lucide-arrow-left class="h-4 w-4" />
                    </a>
                    <h1 class="text-2xl font-semibold tracking-tight">Detail Langkah Kerja</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2 pl-10">
                    <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200" x-text="namaTemplate"></span>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="lingkup === 'Nasional'
                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                        x-text="lingkup"
                    ></span>
                    <template x-if="scopeName">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium"
                            :class="lingkup === 'Nasional'
                                ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'"
                            x-text="scopeName"
                        ></span>
                    </template>
                    <span class="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-semibold text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                        <x-lucide-list-checks class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                        <span x-text="jumlahLangkahKerja"></span>
                        <span>Langkah Kerja</span>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    x-show="selectedIds.length > 0"
                    x-cloak
                    x-on:click="confirmBulkDelete()"
                    x-bind:disabled="bulkDeleting"
                    class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-3.5 text-sm font-medium text-white shadow-xs transition-colors hover:bg-red-700 disabled:opacity-50"
                >
                    <x-lucide-trash-2 class="h-4 w-4" />
                    <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                </button>

                <button
                    type="button"
                    x-on:click="openCreateModal()"
                    class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    <x-lucide-plus class="h-4 w-4" />
                    <span>Tambah Detail Langkah Kerja</span>
                </button>
            </div>
        </div>

        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari uraian langkah kerja..."
            emptyMessage="Belum ada uraian detail langkah kerja pada template ini."
        >
            <x-slot:filters>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-neutral-700 select-none dark:text-neutral-300">
                        <input
                            type="checkbox"
                            x-bind:checked="isAllPageSelected()"
                            x-on:change="toggleSelectAllPage()"
                            class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900"
                        />
                        <span>Pilih Semua di Halaman Ini</span>
                    </label>

                    <div class="flex items-center gap-2" x-show="selectedIds.length > 0" x-cloak>
                        <span class="text-xs text-neutral-600 dark:text-neutral-400">
                            <strong class="text-neutral-900 dark:text-neutral-100" x-text="selectedIds.length"></strong> item dipilih
                        </span>
                        <button
                            type="button"
                            x-on:click="clearSelection()"
                            class="text-xs font-medium text-neutral-500 underline hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200"
                        >
                            Batal Pilih
                        </button>
                    </div>
                </div>
            </x-slot:filters>

            <template x-for="(row, rIndex) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                    :class="isSelected(row.id)
                        ? 'bg-indigo-50/60 dark:bg-indigo-950/30'
                        : (rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950')"
                >
                    {{-- 1. Checkbox --}}
                    <td class="w-12 whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center align-top dark:border-neutral-800">
                        <input
                            type="checkbox"
                            x-bind:checked="isSelected(row.id)"
                            x-on:change="toggleSelectRow(row.id)"
                            class="mt-1 h-4 w-4 cursor-pointer rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900"
                        />
                    </td>

                    {{-- 2. Nomor Urut --}}
                    <td class="w-12 whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center align-top font-semibold text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                    {{-- 3. Uraian Langkah Kerja --}}
                    <td class="border-r border-neutral-200 px-4 py-3 align-top text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                        <div class="tiptap prose prose-sm max-w-none dark:prose-invert" x-html="row.uraian"></div>
                    </td>

                    {{-- 4. Aksi Edit & Delete --}}
                    <td class="w-28 whitespace-nowrap px-3 py-3 text-center align-top">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit Uraian Langkah Kerja"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus Uraian Langkah Kerja"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Tambah / Edit Uraian Langkah Kerja (TipTap) --}}
        <div
            x-show="modalOpen"
            x-cloak
            x-on:keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div
                x-show="modalOpen"
                x-transition.opacity
                x-on:click="closeModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
            ></div>

            <div
                x-show="modalOpen"
                x-transition
                class="relative z-10 my-6 w-full max-w-4xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Uraian Langkah Kerja' : 'Tambah Uraian Langkah Kerja'"></h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Tuliskan rincian uraian langkah kerja menggunakan editor teks.
                        </p>
                    </div>
                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    >
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                {{-- Modal Form --}}
                <form x-on:submit.prevent="saveDetail()" class="flex flex-col max-h-[85vh]">
                    <div class="space-y-4 overflow-y-auto px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" x-text="errors.general[0]"></div>
                        </template>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                                Uraian Langkah Kerja <span class="text-red-500">*</span>
                            </label>

                            {{-- TipTap Editor Area with MS-Word Style Toolbar --}}
                            <div class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                {{-- Toolbar --}}
                                <div class="flex flex-wrap items-center gap-1 border-b border-neutral-200 bg-neutral-50/80 p-2 dark:border-neutral-800 dark:bg-neutral-900/60">
                                    {{-- Undo / Redo --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="undo()"
                                            :disabled="!canUndo()"
                                            title="Undo (Ctrl+Z)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 disabled:opacity-40 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-undo class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="redo()"
                                            :disabled="!canRedo()"
                                            title="Redo (Ctrl+Y)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 disabled:opacity-40 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-redo class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Headings / Paragraph --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="setHeading(0)"
                                            :class="isActive('paragraph') ? 'bg-neutral-200 font-semibold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Paragraf Normal"
                                            class="inline-flex h-8 px-2 items-center justify-center rounded-md text-xs"
                                        >
                                            P
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setHeading(1)"
                                            :class="isActive('heading', { level: 1 }) ? 'bg-neutral-200 font-bold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Heading 1"
                                            class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold"
                                        >
                                            H1
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setHeading(2)"
                                            :class="isActive('heading', { level: 2 }) ? 'bg-neutral-200 font-bold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Heading 2"
                                            class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold"
                                        >
                                            H2
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setHeading(3)"
                                            :class="isActive('heading', { level: 3 }) ? 'bg-neutral-200 font-bold text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Heading 3"
                                            class="inline-flex h-8 w-7 items-center justify-center rounded-md text-xs font-bold"
                                        >
                                            H3
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Formatting: Bold, Italic, Underline, Strike --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="toggleBold()"
                                            :class="isActive('bold') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Tebal (Ctrl+B)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-bold class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleItalic()"
                                            :class="isActive('italic') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Miring (Ctrl+I)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-italic class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleUnderline()"
                                            :class="isActive('underline') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Garis Bawah (Ctrl+U)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-underline class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleStrike()"
                                            :class="isActive('strike') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Coretan (Strikethrough)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-strikethrough class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Pangkat: Superscript, Subscript --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="toggleSuperscript()"
                                            :class="isActive('superscript') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Pangkat Atas (Superscript)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-superscript class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleSubscript()"
                                            :class="isActive('subscript') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Pangkat Bawah (Subscript)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-subscript class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Alignment: Left, Center, Right, Justify --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('left')"
                                            :class="isActive({ textAlign: 'left' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Kiri"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-left class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('center')"
                                            :class="isActive({ textAlign: 'center' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Tengah"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-center class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('right')"
                                            :class="isActive({ textAlign: 'right' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Kanan"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-right class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="setTextAlign('justify')"
                                            :class="isActive({ textAlign: 'justify' }) ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Rata Kiri-Kanan (Justify)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-align-justify class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Lists & Multilevel Indent --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="toggleBulletList()"
                                            :class="isActive('bulletList') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Bullet List"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-list class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="toggleOrderedList()"
                                            :class="isActive('orderedList') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Numbered List"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-list-ordered class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="liftListItem()"
                                            title="Kurangi Indent (Shift+Tab)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-outdent class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="sinkListItem()"
                                            title="Tambah Indent (Tab / Multilevel)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-indent class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Table Controls --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="insertTable(3, 3)"
                                            title="Sisipkan Tabel (3x3)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-table class="h-4 w-4" />
                                        </button>
                                        <template x-if="isActive('table')">
                                            <div class="inline-flex items-center gap-0.5">
                                                <button
                                                    type="button"
                                                    x-on:click="addRowAfter()"
                                                    title="Tambah Baris Bawah"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                                >
                                                    +Baris
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="addColumnAfter()"
                                                    title="Tambah Kolom Kanan"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                                >
                                                    +Kolom
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="deleteRow()"
                                                    title="Hapus Baris"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                                >
                                                    -Baris
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="deleteColumn()"
                                                    title="Hapus Kolom"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                                >
                                                    -Kolom
                                                </button>
                                                <button
                                                    type="button"
                                                    x-on:click="deleteTable()"
                                                    title="Hapus Tabel"
                                                    class="inline-flex h-8 px-1.5 items-center justify-center rounded-md text-[11px] font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                                >
                                                    Hapus Tabel
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="h-5 w-px bg-neutral-300 dark:bg-neutral-700 mx-1"></div>

                                    {{-- Embed, Link, Image & Clear Formatting --}}
                                    <div class="inline-flex items-center gap-0.5">
                                        <button
                                            type="button"
                                            x-on:click="setLink()"
                                            :class="isActive('link') ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-800 dark:text-white' : 'text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                            title="Sisipkan Link"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        >
                                            <x-lucide-link class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="insertImage()"
                                            title="Sisipkan Gambar / Embed"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-image class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="clearFormatting()"
                                            title="Bersihkan Format"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-neutral-700 hover:bg-neutral-200/70 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                        >
                                            <x-lucide-remove-formatting class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                {{-- Editor Canvas --}}
                                <div
                                    x-ref="editor_uraian"
                                    x-on:click="focusEditor($event)"
                                    class="tiptap min-h-[280px] max-h-[460px] overflow-y-auto cursor-text bg-white dark:bg-neutral-950"
                                ></div>
                            </div>

                            <template x-if="errors.uraian">
                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian[0]"></p>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                        <button
                            type="button"
                            x-on:click="closeModal()"
                            class="inline-flex h-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            x-bind:disabled="saving"
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-5 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                        >
                            <template x-if="saving">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white dark:border-neutral-900/20 dark:border-t-neutral-900"></span>
                            </template>
                            <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan Langkah Kerja')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
