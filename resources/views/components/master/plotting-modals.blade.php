{{-- Modal Create / Edit Plotting --}}
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
        class="relative z-10 w-full max-w-2xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
            <div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Plotting Wilayah' : 'Tambah Plotting Wilayah'"></h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Tentukan nama plot (contoh: WIL I) dan pilih provinsi mana saja yang masuk ke dalam plot tersebut.
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

        <form x-on:submit.prevent="savePlotting()" class="space-y-5 px-6 py-5">
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                    Nama Plot <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    x-model="form.plot"
                    placeholder="Contoh: WIL I"
                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-white/20"
                />
                <template x-if="errors.plot">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.plot[0]"></p>
                </template>
            </div>

            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Pilih Provinsi <span class="text-red-500">*</span>
                        <span class="ml-1 font-normal text-neutral-500" x-text="`(${form.provinsi_ids.length} dipilih)`"></span>
                    </label>

                    <div class="flex items-center gap-2 text-xs">
                        <button
                            type="button"
                            x-on:click="selectAllFilteredProvinces()"
                            class="font-medium text-neutral-700 underline-offset-2 hover:underline dark:text-neutral-300"
                        >
                            Pilih Semua
                        </button>
                        <span class="text-neutral-300 dark:text-neutral-700">|</span>
                        <button
                            type="button"
                            x-on:click="clearSelectedProvinces()"
                            class="font-medium text-neutral-500 underline-offset-2 hover:underline dark:text-neutral-400"
                        >
                            Reset Pilihan
                        </button>
                    </div>
                </div>

                <div
                    x-show="form.provinsi_ids.length > 0"
                    class="flex max-h-24 flex-wrap gap-1.5 overflow-y-auto rounded-lg border border-neutral-200 bg-neutral-50/70 p-2 dark:border-neutral-800 dark:bg-neutral-900/50"
                >
                    <template x-for="id in form.provinsi_ids" :key="id">
                        <span class="inline-flex items-center gap-1 rounded-md border border-neutral-200 bg-white px-2 py-0.5 text-xs font-medium text-neutral-800 shadow-2xs dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                            <span x-text="getProvinceById(id)?.name || id"></span>
                            <button
                                type="button"
                                x-on:click="toggleProvince(id)"
                                class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200"
                            >
                                <x-lucide-x class="h-3 w-3" />
                            </button>
                        </span>
                    </template>
                </div>

                <div class="relative">
                    <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
                    <input
                        type="search"
                        x-model="provinceQuery"
                        placeholder="Cari nama provinsi..."
                        class="block h-8 w-full rounded-md border border-neutral-200 bg-white pl-8 pr-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                </div>

                <div class="max-h-60 overflow-y-auto rounded-lg border border-neutral-200 divide-y divide-neutral-100 dark:border-neutral-800 dark:divide-neutral-800/60">
                    <template x-if="filteredModalProvinces.length === 0">
                        <div class="px-4 py-6 text-center text-xs text-neutral-500">
                            Provinsi tidak ditemukan.
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2">
                        <template x-for="prov in filteredModalProvinces" :key="prov.id">
                            <label
                                class="flex cursor-pointer items-center gap-2.5 border-b border-neutral-100 px-3 py-2 text-xs hover:bg-neutral-50 dark:border-neutral-800/60 dark:hover:bg-neutral-900/60"
                                :class="isProvinceSelected(prov.id) ? 'bg-neutral-100/70 font-medium dark:bg-neutral-800/50' : ''"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isProvinceSelected(prov.id)"
                                    x-on:change="toggleProvince(prov.id)"
                                    class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900 dark:border-neutral-700 dark:bg-neutral-900"
                                />
                                <span class="truncate text-neutral-800 dark:text-neutral-200" x-text="prov.name"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <template x-if="errors.provinsi_ids">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.provinsi_ids[0]"></p>
                </template>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-neutral-200 pt-4 dark:border-neutral-800">
                <button
                    type="button"
                    x-on:click="closeModal()"
                    class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    x-bind:disabled="saving"
                    class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    <x-lucide-loader-circle x-show="saving" x-cloak class="h-4 w-4 animate-spin" />
                    <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan Plotting'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div
    x-show="deleteModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div
        x-show="deleteModalOpen"
        x-transition.opacity
        x-on:click="deleteModalOpen = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
    ></div>

    <div
        x-show="deleteModalOpen"
        x-transition
        class="relative z-10 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Data Plotting</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin menghapus plot <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="deletingItem?.plot"></span>? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex items-center justify-end gap-2">
            <button
                type="button"
                x-on:click="deleteModalOpen = false"
                class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                Batal
            </button>
            <button
                type="button"
                x-on:click="deletePlotting()"
                x-bind:disabled="deleting"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-4 text-sm font-medium text-white shadow-xs hover:bg-red-700 disabled:opacity-50"
            >
                <x-lucide-loader-circle x-show="deleting" x-cloak class="h-4 w-4 animate-spin" />
                <span>Hapus</span>
            </button>
        </div>
    </div>
</div>
