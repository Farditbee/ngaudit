<x-admin-layout :breadcrumbs="['Data Master', 'User']">
    <div class="space-y-6" x-data="userDataTable(@js($tableConfig))">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Data User</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Kelola akun pengguna, peran (Super Admin, Auditor, Auditee, Ketua Tim, Koordinator, Pengawas), serta penugasan wilayah.
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah User</span>
            </button>
        </div>

        {{-- Flash Alert --}}
        <div
            x-show="flashMessage !== ''"
            x-cloak
            x-transition.opacity
            class="flex items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200"
        >
            <div class="flex items-center gap-2">
                <x-lucide-circle-check class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <span x-text="flashMessage"></span>
            </div>
            <button type="button" x-on:click="flashMessage = ''" class="text-emerald-700 hover:text-emerald-950 dark:text-emerald-300">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        {{-- DataTable --}}
        <x-data-table
            searchPlaceholder="Cari nama, username, atau email..."
            emptyMessage="Tidak ada data user yang ditemukan."
        >
            <x-slot:filters>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Filter Peran"
                        placeholder="Semua Peran"
                        itemsExpr="roleOptions"
                        valueModel="filterRole"
                        changeAction="onFilterChange()"
                    />
                </div>

                <div class="mt-3 flex justify-end" x-show="filterRole !== ''" x-cloak>
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

            <template x-for="(row, rIndex) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                    :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                >
                    <td class="whitespace-nowrap border-r border-neutral-200 px-3 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                    <td class="max-w-[11rem] border-r border-neutral-200 px-3 py-3 font-medium text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                        <div class="break-words" x-text="row.name"></div>
                    </td>
                    <td class="max-w-[11rem] border-r border-neutral-200 px-3 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                        <div class="break-words" x-text="row.jabatan || '-'"></div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.username || '-'"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.email"></td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <div class="space-y-1.5">
                            <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                                <span x-text="row.role || '-'"></span>
                                <template x-if="row.level">
                                    <span>
                                        ( <span class="text-red-500 dark:text-red-400" x-text="row.level"></span> ) :
                                    </span>
                                </template>
                            </div>

                            <template x-if="row.level">
                                <div class="flex flex-col items-start gap-1">
                                    <template x-if="row.level === 'Nasional' && row.plot_name">
                                        <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[10px] leading-tight font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300" x-text="row.plot_name"></span>
                                    </template>
                                    <template x-if="row.province_name">
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] leading-tight font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300" x-text="row.province_name"></span>
                                    </template>
                                    <template x-if="row.city_name">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] leading-tight font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300" x-text="row.city_name"></span>
                                    </template>
                                    <template x-if="row.district_name">
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] leading-tight font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300" x-text="row.district_name"></span>
                                    </template>
                                    <template x-if="row.village_name">
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] leading-tight font-medium text-red-700 dark:bg-red-900/40 dark:text-red-300" x-text="row.village_name"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </td>
                    <td class="whitespace-nowrap border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                        <template x-if="row.bidang">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300': row.bidang === 'Operasional',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300': row.bidang === 'Manajemen Anggaran dan Keuangan',
                                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300': row.bidang === 'Logistik',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': row.bidang === 'Sumber Daya Manusia',
                                    'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300': !['Operasional', 'Manajemen Anggaran dan Keuangan', 'Logistik', 'Sumber Daya Manusia'].includes(row.bidang)
                                }"
                                x-text="row.bidang"
                            ></span>
                        </template>
                        <template x-if="!row.bidang">
                            <span class="text-neutral-400">-</span>
                        </template>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-center">
                        <div class="inline-grid grid-cols-2 gap-1.5">
                            <button
                                type="button"
                                x-on:click="confirmImpersonate(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-indigo-200 bg-white text-indigo-600 hover:bg-indigo-50 dark:border-indigo-900/50 dark:bg-neutral-900 dark:text-indigo-400 dark:hover:bg-indigo-950/40"
                                title="Impersonate (Masuk sebagai User ini)"
                            >
                                <x-lucide-user-check class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmResetPassword(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-amber-200 bg-white text-amber-600 hover:bg-amber-50 dark:border-amber-900/50 dark:bg-neutral-900 dark:text-amber-400 dark:hover:bg-amber-950/40"
                                title="Reset Password (Password123)"
                            >
                                <x-lucide-key-round class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="openEditModal(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                title="Edit User"
                            >
                                <x-lucide-pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus User"
                            >
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        <x-master.user-modals />
    </div>
</x-admin-layout>
