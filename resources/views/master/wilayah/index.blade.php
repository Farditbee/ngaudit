<x-admin-layout :breadcrumbs="['Data Master', 'Wilayah']">
    <div class="space-y-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight">Data Wilayah Indonesia</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Daftar kode dan nama Provinsi, Kabupaten/Kota, Kecamatan, serta Desa/Kelurahan di seluruh Indonesia.
            </p>
        </div>

        <div
            class=" rounded-xl bg-white shadow-sm ring-1 ring-neutral-900/10 dark:bg-neutral-950 dark:ring-white/10"
            x-data="{
                rows: [],
                loading: true,
                draw: 0,
                page: 1,
                perPage: 10,
                total: 0,
                filtered: 0,
                search: '',
                sortCol: 1,
                sortDir: 'asc',
                provinceCode: '',
                cityCode: '',
                districtCode: '',
                provinces: @js($provinces),
                cities: [],
                districts: [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', label: '#', sortable: false, searchable: false },
                    { data: 'code', name: 'code', label: 'KODE', sortable: true, searchable: true },
                    { data: 'province_name', name: 'province_name', label: 'PROVINSI', sortable: true, searchable: true },
                    { data: 'city_name', name: 'city_name', label: 'KABUPATEN/KOTA', sortable: true, searchable: true },
                    { data: 'district_name', name: 'district_name', label: 'KECAMATAN', sortable: true, searchable: true },
                    { data: 'village_name', name: 'village_name', label: 'DESA/KELURAHAN', sortable: true, searchable: true },
                ],
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filtered / this.perPage));
                },
                get startEntry() {
                    return this.filtered === 0 ? 0 : (this.page - 1) * this.perPage + 1;
                },
                get endEntry() {
                    return Math.min(this.page * this.perPage, this.filtered);
                },
                get visiblePages() {
                    const total = this.totalPages;
                    const current = this.page;
                    if (total <= 7) {
                        return Array.from({ length: total }, (_, i) => i + 1);
                    }
                    const pages = [1];
                    if (current > 3) pages.push('...');
                    const start = Math.max(2, current - 1);
                    const end = Math.min(total - 1, current + 1);
                    for (let i = start; i <= end; i++) pages.push(i);
                    if (current < total - 2) pages.push('...');
                    pages.push(total);
                    return pages;
                },
                init() {
                    this.fetchData();
                },
                async fetchOptions(type, parent) {
                    const params = new URLSearchParams({ options: type, parent });
                    const response = await fetch(`{{ route('master.wilayah.index') }}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    return await response.json();
                },
                async onProvinceChange() {
                    this.cityCode = '';
                    this.districtCode = '';
                    this.cities = [];
                    this.districts = [];
                    this.page = 1;
                    this.fetchData();
                    if (this.provinceCode) {
                        this.cities = await this.fetchOptions('cities', this.provinceCode);
                    }
                },
                async onCityChange() {
                    this.districtCode = '';
                    this.districts = [];
                    this.page = 1;
                    this.fetchData();
                    if (this.cityCode) {
                        this.districts = await this.fetchOptions('districts', this.cityCode);
                    }
                },
                onDistrictChange() {
                    this.page = 1;
                    this.fetchData();
                },
                resetFilters() {
                    this.provinceCode = '';
                    this.cityCode = '';
                    this.districtCode = '';
                    this.cities = [];
                    this.districts = [];
                    this.search = '';
                    this.page = 1;
                    this.fetchData();
                },
                buildParams() {
                    this.draw++;
                    const params = new URLSearchParams();
                    params.set('draw', this.draw);
                    params.set('start', (this.page - 1) * this.perPage);
                    params.set('length', this.perPage);
                    params.set('search[value]', this.search);
                    params.set('search[regex]', 'false');
                    if (this.provinceCode) params.set('province_code', this.provinceCode);
                    if (this.cityCode) params.set('city_code', this.cityCode);
                    if (this.districtCode) params.set('district_code', this.districtCode);
                    this.columns.forEach((col, i) => {
                        params.set(`columns[${i}][data]`, col.data);
                        params.set(`columns[${i}][name]`, col.name);
                        params.set(`columns[${i}][searchable]`, col.searchable ? 'true' : 'false');
                        params.set(`columns[${i}][orderable]`, col.sortable ? 'true' : 'false');
                        params.set(`columns[${i}][search][value]`, '');
                        params.set(`columns[${i}][search][regex]`, 'false');
                    });
                    params.set('order[0][column]', this.sortCol);
                    params.set('order[0][dir]', this.sortDir);
                    return params.toString();
                },
                async fetchData() {
                    this.loading = true;
                    const currentDraw = this.draw + 1;
                    try {
                        const response = await fetch(`{{ route('master.wilayah.index') }}?${this.buildParams()}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const json = await response.json();
                        if (Number(json.draw) === currentDraw) {
                            this.rows = json.data || [];
                            this.total = json.recordsTotal || 0;
                            this.filtered = json.recordsFiltered || 0;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },
                toggleSort(index) {
                    if (!this.columns[index].sortable) return;
                    if (this.sortCol === index) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortCol = index;
                        this.sortDir = 'asc';
                    }
                    this.page = 1;
                    this.fetchData();
                },
                goToPage(p) {
                    if (p === '...' || p < 1 || p > this.totalPages || p === this.page) return;
                    this.page = p;
                    this.fetchData();
                },
                onSearchInput() {
                    this.page = 1;
                    this.fetchData();
                },
                onPerPageChange() {
                    this.page = 1;
                    this.fetchData();
                }
            }"
        >
            {{-- Hierarchical Filter Bar --}}
            <div class="rounded-t-xl border-b border-neutral-200 bg-neutral-50/40 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                <div class="grid gap-4 md:grid-cols-3">
                    {{-- Provinsi --}}
                    <x-searchable-select
                        label="Provinsi"
                        placeholder="Semua Provinsi"
                        itemsExpr="provinces"
                        valueModel="provinceCode"
                        changeAction="onProvinceChange()"
                    />

                    {{-- Kabupaten/Kota --}}
                    <div x-show="provinceCode !== ''" x-cloak x-transition.opacity>
                        <x-searchable-select
                            label="Kabupaten / Kota"
                            placeholder="Semua Kabupaten/Kota"
                            itemsExpr="cities"
                            valueModel="cityCode"
                            changeAction="onCityChange()"
                        />
                    </div>

                    {{-- Kecamatan --}}
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
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                <div class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                    <span>Show</span>
                    <select
                        x-model.number="perPage"
                        x-on:change="onPerPageChange()"
                        class="h-9 rounded-md border border-neutral-200 bg-white py-1 pl-3 pr-8 text-sm text-neutral-900 shadow-xs focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-white/20"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>

                <div class="relative w-full sm:w-72">
                    <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                    <input
                        type="search"
                        x-model="search"
                        x-on:input.debounce.350ms="onSearchInput()"
                        placeholder="Cari kode atau nama wilayah..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white pl-9 pr-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:ring-white/20"
                    />
                </div>
            </div>

            {{-- Table --}}
            <div class="relative overflow-x-auto">
                <div
                    x-show="loading"
                    x-cloak
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-[1px] dark:bg-neutral-950/60"
                >
                    <div class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3.5 py-2 text-xs font-medium shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <x-lucide-loader-circle class="h-4 w-4 animate-spin" />
                        <span>Memuat data...</span>
                    </div>
                </div>

                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 bg-neutral-50/70 text-xs uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400">
                            <template x-for="(col, index) in columns" :key="col.name">
                                <th
                                    class="border-r border-neutral-200 px-4 py-3 font-medium select-none last:border-r-0 dark:border-neutral-800"
                                    :class="[
                                        col.sortable ? 'cursor-pointer hover:bg-neutral-100/80 dark:hover:bg-neutral-800/60' : '',
                                        index === 0 ? 'w-16 text-center' : ''
                                    ]"
                                    x-on:click="toggleSort(index)"
                                >
                                    <div class="flex items-center justify-between gap-2" :class="index === 0 ? 'justify-center' : ''">
                                        <span x-text="col.label"></span>
                                        <template x-if="col.sortable">
                                            <span class="inline-flex flex-col text-neutral-400">
                                                <x-lucide-chevrons-up-down
                                                    x-show="sortCol !== index"
                                                    class="h-3.5 w-3.5 opacity-60"
                                                />
                                                <x-lucide-chevron-up
                                                    x-show="sortCol === index && sortDir === 'asc'"
                                                    x-cloak
                                                    class="h-3.5 w-3.5 text-neutral-900 dark:text-neutral-100"
                                                />
                                                <x-lucide-chevron-down
                                                    x-show="sortCol === index && sortDir === 'desc'"
                                                    x-cloak
                                                    class="h-3.5 w-3.5 text-neutral-900 dark:text-neutral-100"
                                                />
                                            </span>
                                        </template>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="!loading && rows.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                    Tidak ada data wilayah yang ditemukan.
                                </td>
                            </tr>
                        </template>

                        <template x-for="(row, rIndex) in rows" :key="row.code + '-' + rIndex">
                            <tr
                                class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                                :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                            >
                                <td class="border-r border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                                <td class="border-r border-neutral-200 px-4 py-3 font-mono text-neutral-900 dark:border-neutral-800 dark:text-neutral-100" x-text="row.code"></td>
                                <td class="border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.province_name"></td>
                                <td class="border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.city_name"></td>
                                <td class="border-r border-neutral-200 px-4 py-3 text-neutral-800 dark:border-neutral-800 dark:text-neutral-200" x-text="row.district_name"></td>
                                <td class="px-4 py-3 text-neutral-800 dark:text-neutral-200" x-text="row.village_name"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-neutral-200 px-6 py-4 text-sm dark:border-neutral-800">
                <p class="text-neutral-500 dark:text-neutral-400">
                    Showing
                    <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="startEntry.toLocaleString('id-ID')"></span>
                    to
                    <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="endEntry.toLocaleString('id-ID')"></span>
                    of
                    <span class="font-medium text-neutral-900 dark:text-neutral-100" x-text="filtered.toLocaleString('id-ID')"></span>
                    entries
                    <template x-if="filtered !== total">
                        <span>(filtered from <span x-text="total.toLocaleString('id-ID')"></span> total entries)</span>
                    </template>
                </p>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        x-on:click="goToPage(page - 1)"
                        x-bind:disabled="page <= 1 || loading"
                        class="flex h-8 items-center gap-1 rounded-md px-2.5 text-sm hover:bg-neutral-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-neutral-800"
                    >
                        <x-lucide-chevron-left class="h-4 w-4" />
                        <span>Previous</span>
                    </button>

                    <template x-for="(p, i) in visiblePages" :key="p + '-' + i">
                        <button
                            type="button"
                            x-on:click="goToPage(p)"
                            x-bind:disabled="p === '...' || loading"
                            x-text="p"
                            class="h-8 min-w-8 rounded-md px-2 text-sm"
                            x-bind:class="page === p
                                ? 'border border-neutral-200 bg-neutral-100 font-medium text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100'
                                : (p === '...' ? 'cursor-default text-neutral-400' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800')"
                        ></button>
                    </template>

                    <button
                        type="button"
                        x-on:click="goToPage(page + 1)"
                        x-bind:disabled="page >= totalPages || loading"
                        class="flex h-8 items-center gap-1 rounded-md px-2.5 text-sm hover:bg-neutral-100 disabled:pointer-events-none disabled:opacity-50 dark:hover:bg-neutral-800"
                    >
                        <span>Next</span>
                        <x-lucide-chevron-right class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
