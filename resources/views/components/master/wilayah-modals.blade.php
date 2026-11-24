{{-- Modal Tambah / Edit Wilayah --}}
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
        class="relative z-10 w-full max-w-2xl overflow-visible rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
            <div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingTarget ? 'Edit Data Wilayah' : 'Tambah Data Wilayah'"></h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Pilih tingkat wilayah, tentukan wilayah induknya, serta masukkan kode unik yang mengikuti kode induknya.
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

        <form x-on:submit.prevent="saveWilayah()" class="space-y-4 px-6 py-5">
            {{-- Tingkat Wilayah --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Tingkat Wilayah</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <template x-for="item in [
                        { key: 'province', label: 'Provinsi' },
                        { key: 'city', label: 'Kabupaten/Kota' },
                        { key: 'district', label: 'Kecamatan' },
                        { key: 'village', label: 'Desa/Kelurahan' }
                    ]" :key="item.key">
                        <button
                            type="button"
                            x-bind:disabled="Boolean(editingTarget)"
                            x-on:click="form.level = item.key; onModalLevelChange()"
                            class="h-9 rounded-md border px-3 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-60"
                            x-bind:class="form.level === item.key
                                ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-900'
                                : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                            x-text="item.label"
                        ></button>
                    </template>
                </div>
            </div>

            {{-- Provinsi --}}
            <template x-if="form.level === 'province'">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Provinsi <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.province_name"
                        placeholder="Masukkan nama provinsi..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.province_name">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.province_name[0]"></p>
                    </template>
                </div>
            </template>

            <template x-if="form.level !== 'province'">
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Provinsi Induk *"
                        placeholder="Pilih Provinsi"
                        itemsExpr="provinces"
                        valueModel="form.province_code"
                        changeAction="onModalProvinceChange()"
                    />
                    <template x-if="errors.province_code">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.province_code[0]"></p>
                    </template>
                </div>
            </template>

            {{-- Kabupaten/Kota --}}
            <template x-if="form.level === 'city'">
                <div class="space-y-1.5" x-data="{ showHint: false }">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Kabupaten / Kota <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.city_name"
                        x-bind:readonly="!form.province_code"
                        x-on:click="if (!form.province_code) showHint = true"
                        x-on:focus="if (!form.province_code) showHint = true"
                        x-effect="if (form.province_code) showHint = false"
                        placeholder="Masukkan nama kabupaten/kota (misal: KABUPATEN ACEH SELATAN)..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <p
                        x-show="showHint && !form.province_code"
                        x-cloak
                        class="flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400"
                    >
                        <x-lucide-info class="h-3.5 w-3.5 shrink-0" />
                        <span>Silahkan pilih provinsi terlebih dahulu</span>
                    </p>
                    <template x-if="errors.city_name">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.city_name[0]"></p>
                    </template>
                </div>
            </template>

            <template x-if="form.level === 'district' || form.level === 'village'">
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Kabupaten / Kota Induk *"
                        placeholder="Pilih Kabupaten/Kota"
                        itemsExpr="modalCities"
                        valueModel="form.city_code"
                        changeAction="onModalCityChange()"
                        disabledExpr="!form.province_code"
                        disabledMessage="Silahkan pilih provinsi terlebih dahulu"
                    />
                    <template x-if="errors.city_code">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.city_code[0]"></p>
                    </template>
                </div>
            </template>

            {{-- Kecamatan --}}
            <template x-if="form.level === 'district'">
                <div class="space-y-1.5" x-data="{ showHint: false }">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Kecamatan <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.district_name"
                        x-bind:readonly="!form.city_code"
                        x-on:click="if (!form.city_code) showHint = true"
                        x-on:focus="if (!form.city_code) showHint = true"
                        x-effect="if (form.city_code) showHint = false"
                        placeholder="Masukkan nama kecamatan..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <p
                        x-show="showHint && !form.city_code"
                        x-cloak
                        class="flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400"
                    >
                        <x-lucide-info class="h-3.5 w-3.5 shrink-0" />
                        <span x-text="!form.province_code ? 'Silahkan pilih provinsi terlebih dahulu' : 'Silahkan pilih kabupaten/kota terlebih dahulu'"></span>
                    </p>
                    <template x-if="errors.district_name">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.district_name[0]"></p>
                    </template>
                </div>
            </template>

            <template x-if="form.level === 'village'">
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Kecamatan Induk *"
                        placeholder="Pilih Kecamatan"
                        itemsExpr="modalDistricts"
                        valueModel="form.district_code"
                        changeAction="onModalDistrictChange()"
                        disabledExpr="!form.city_code"
                        disabledMessage="Silahkan pilih kabupaten/kota terlebih dahulu"
                    />
                    <template x-if="errors.district_code">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.district_code[0]"></p>
                    </template>
                </div>
            </template>

            {{-- Desa / Kelurahan --}}
            <template x-if="form.level === 'village'">
                <div class="space-y-1.5" x-data="{ showHint: false }">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Desa / Kelurahan <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.village_name"
                        x-bind:readonly="!form.district_code"
                        x-on:click="if (!form.district_code) showHint = true"
                        x-on:focus="if (!form.district_code) showHint = true"
                        x-effect="if (form.district_code) showHint = false"
                        placeholder="Masukkan nama desa/kelurahan..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <p
                        x-show="showHint && !form.district_code"
                        x-cloak
                        class="flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400"
                    >
                        <x-lucide-info class="h-3.5 w-3.5 shrink-0" />
                        <span x-text="!form.province_code ? 'Silahkan pilih provinsi terlebih dahulu' : (!form.city_code ? 'Silahkan pilih kabupaten/kota terlebih dahulu' : 'Silahkan pilih kecamatan terlebih dahulu')"></span>
                    </p>
                    <template x-if="errors.village_name">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.village_name[0]"></p>
                    </template>
                </div>
            </template>

            {{-- Kode Wilayah --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Kode Wilayah <span class="text-red-500">*</span>
                    </label>
                    <template x-if="parentCodePrefix">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                            Wajib diawali kode induk: <strong class="font-mono text-neutral-800 dark:text-neutral-200" x-text="parentCodePrefix"></strong>
                        </span>
                    </template>
                </div>
                <input
                    type="text"
                    x-model="form.code"
                    x-bind:placeholder="codePlaceholder"
                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 font-mono text-sm text-neutral-900 shadow-xs placeholder:font-sans placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                />
                <template x-if="errors.code">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.code[0]"></p>
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
                    <span x-text="editingTarget ? 'Simpan Perubahan' : 'Simpan Wilayah'"></span>
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
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Data Wilayah</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin menghapus <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="getDeletingLabel(deletingItem)"></span>?
        </p>

        <template x-if="deleteError">
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300" x-text="deleteError"></div>
        </template>

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
                x-on:click="deleteWilayah()"
                x-bind:disabled="deleting"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-4 text-sm font-medium text-white shadow-xs hover:bg-red-700 disabled:opacity-50"
            >
                <x-lucide-loader-circle x-show="deleting" x-cloak class="h-4 w-4 animate-spin" />
                <span>Hapus</span>
            </button>
        </div>
    </div>
</div>
