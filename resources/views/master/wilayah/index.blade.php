<x-admin-layout :breadcrumbs="['Data Master', 'Wilayah']">
    <div class="space-y-6" x-data="wilayahDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Data Wilayah</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Daftar kode dan nama Provinsi, Kabupaten/Kota, Kecamatan, serta Desa/Kelurahan di seluruh Indonesia.
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Wilayah</span>
            </button>
        </div>


        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari kode atau nama wilayah..."
            emptyMessage="Tidak ada data wilayah yang ditemukan."
        >
            <x-slot:filters>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Provinsi"
                        placeholder="Semua Provinsi"
                        itemsExpr="provinces"
                        valueModel="provinceCode"
                        changeAction="onProvinceChange()"
                    />

                    <div x-show="provinceCode !== ''" x-cloak x-transition.opacity>
                        <x-searchable-select
                            label="Kabupaten / Kota"
                            placeholder="Semua Kabupaten/Kota"
                            itemsExpr="cities"
                            valueModel="cityCode"
                            changeAction="onCityChange()"
                        />
                    </div>

                    <div x-show="cityCode !== ''" x-cloak x-transition.opacity>
                        <x-searchable-select
                            label="Kecamatan"
                            placeholder="Semua Kecamatan"
                            itemsExpr="districts"
                            valueModel="districtCode"
                            changeAction="onDistrictChange()"
                        />
                    </div>
                </div>

                <div class="mt-3 flex justify-end" x-show="provinceCode !== ''" x-cloak>
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

            <template x-for="(row, rIndex) in rows" :key="row.code + '-' + rIndex">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                    :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                >
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 font-mono text-neutral-900 dark:border-neutral-800 dark:text-neutral-100" x-text="row.code"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.province_name"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.city_name"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.district_name"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.village_name"></td>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit Wilayah"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus Wilayah"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        <x-master.wilayah-modals />
    </div>
</x-admin-layout>
