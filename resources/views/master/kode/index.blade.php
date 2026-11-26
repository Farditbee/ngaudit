<x-admin-layout :breadcrumbs="['Data Master', 'Kode', $title]">
    <div class="space-y-6" x-data="kodeDataTable(@js($tableConfig))">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    @if ($isSub)
                        Kelola sub kode {{ $jenis }} yang terikat langsung pada kode {{ $jenis }} utamanya.
                    @elseif ($requiresTemuan)
                        Kelola daftar kode {{ $jenis }} yang terikat pada konteks kode temuan beserta jumlah sub kode {{ $jenis }} di bawahnya.
                    @else
                        Kelola daftar kode {{ $jenis }} beserta jumlah sub kode yang terikat.
                    @endif
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah {{ $title }}</span>
            </button>
        </div>

        <x-data-table
            searchPlaceholder="Cari kode atau nama..."
            emptyMessage="Belum ada data. Klik tombol Tambah untuk menambahkan data baru."
        >
            @if ($isSub || $requiresTemuan)
                <x-slot:filters>
                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($isSub)
                            <x-searchable-select
                                label="Filter Kode {{ ucfirst($jenis) }} (Induk)"
                                placeholder="Semua Kode {{ ucfirst($jenis) }}"
                                itemsExpr="parents"
                                valueModel="filterKodeId"
                                changeAction="onFilterChange()"
                            />
                        @else
                            <x-searchable-select
                                label="Filter Kode Temuan"
                                placeholder="Semua Kode Temuan"
                                itemsExpr="parents"
                                valueModel="filterKodeTemuanId"
                                changeAction="onFilterChange()"
                            />
                        @endif
                    </div>

                    <div class="mt-3 flex justify-end" x-show="filterKodeId !== '' || filterKodeTemuanId !== ''" x-cloak>
                        <button
                            type="button"
                            x-on:click="resetFilters()"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-3 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        >
                            <x-lucide-rotate-ccw class="h-3.5 w-3.5" />
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </x-slot:filters>
            @endif

            <template x-for="(row, rIndex) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                    :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                >
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                    <template x-if="requiresTemuan">
                        <td class="border-r border-neutral-200 px-4 py-3 text-neutral-700 dark:border-neutral-800 dark:text-neutral-300" x-text="row.temuan_label"></td>
                    </template>
                    <template x-if="isSub">
                        <td class="border-r border-neutral-200 px-4 py-3 text-neutral-700 dark:border-neutral-800 dark:text-neutral-300" x-text="row.parent_label"></td>
                    </template>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 font-semibold text-neutral-900 dark:border-neutral-800 dark:text-neutral-100" x-text="row.kode"></td>
                    <td class="border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.nama"></td>
                    <template x-if="!isSub">
                        <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center dark:border-neutral-800">
                            <span class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200" x-text="`${row.sub_kodes_count || 0} Sub Kode`"></span>
                        </td>
                    </template>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Create / Edit --}}
        <div
            x-show="modalOpen"
            x-cloak
            x-on:keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
        >
            <div x-show="modalOpen" x-transition.opacity x-on:click="closeModal()" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

            <div x-show="modalOpen" x-transition class="relative z-10 w-full max-w-lg overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                    <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="(editingId ? 'Edit ' : 'Tambah ') + @js($title)"></h2>
                    <button type="button" x-on:click="closeModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <form x-on:submit.prevent="saveKode()" class="space-y-5 px-6 py-5">
                    <template x-if="requiresTemuan">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                Kode Temuan <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="form.kode_temuan_id"
                                class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                            >
                                <option value="">Pilih kode temuan</option>
                                <template x-for="parent in parents" :key="parent.id">
                                    <option :value="String(parent.id)" x-text="parent.name"></option>
                                </template>
                            </select>
                            <template x-if="errors.kode_temuan_id">
                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode_temuan_id[0]"></p>
                            </template>
                        </div>
                    </template>

                    <template x-if="isSub">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                Kode {{ ucfirst($jenis) }} (Induk) <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="form.kode_id"
                                class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                            >
                                <option value="">Pilih kode {{ $jenis }} induk</option>
                                <template x-for="parent in parents" :key="parent.id">
                                    <option :value="String(parent.id)" x-text="parent.name"></option>
                                </template>
                            </select>
                            <template x-if="errors.kode_id">
                                <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode_id[0]"></p>
                            </template>
                        </div>
                    </template>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            <span x-text="isSub ? 'Sub Kode' : 'Kode'"></span> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.kode"
                            :placeholder="isSub ? 'Contoh: 01.01' : 'Contoh: 01'"
                            class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                        />
                        <template x-if="errors.kode">
                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.nama"
                            class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                        />
                        <template x-if="errors.nama">
                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nama[0]"></p>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-neutral-200 pt-4 dark:border-neutral-800">
                        <button type="button" x-on:click="closeModal()" class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800">
                            Batal
                        </button>
                        <button type="submit" x-bind:disabled="saving" class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                            <x-lucide-loader-circle x-show="saving" x-cloak class="h-4 w-4 animate-spin" />
                            <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
