<x-admin-layout :breadcrumbs="['Data Master', 'Plotting']">
    <div
        class="space-y-6"
        x-data="{
            rows: [],
            loading: true,
            saving: false,
            deleting: false,
            draw: 0,
            page: 1,
            perPage: 10,
            total: 0,
            filtered: 0,
            search: '',
            sortCol: 1,
            sortDir: 'asc',
            filterProvinsiId: '',
            provinces: @js($provinces),
            modalOpen: false,
            deleteModalOpen: false,
            editingId: null,
            deletingItem: null,
            provinceQuery: '',
            flashMessage: '',
            form: {
                plot: '',
                provinsi_ids: [],
            },
            errors: {},
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', label: '#', sortable: false, searchable: false },
                { data: 'plot', name: 'plot', label: 'NAMA PLOT', sortable: true, searchable: true },
                { data: 'provinces_names', name: 'provinces_names', label: 'DAFTAR PROVINSI', sortable: false, searchable: true },
                { data: 'provinces_count', name: 'provinces_count', label: 'JUMLAH PROVINSI', sortable: true, searchable: false },
                { data: 'actions', name: 'actions', label: 'AKSI', sortable: false, searchable: false },
            ],
            get filterProvinces() {
                return this.provinces.map(p => ({
                    code: String(p.id),
                    name: p.name,
                }));
            },
            get filteredModalProvinces() {
                const q = this.provinceQuery.trim().toLowerCase();
                if (!q) return this.provinces;
                return this.provinces.filter(p =>
                    p.name.toLowerCase().includes(q)
                );
            },
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
            showFlash(message) {
                this.flashMessage = message;
                setTimeout(() => {
                    if (this.flashMessage === message) {
                        this.flashMessage = '';
                    }
                }, 3500);
            },
            onFilterChange() {
                this.page = 1;
                this.fetchData();
            },
            resetFilters() {
                this.filterProvinsiId = '';
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
                if (this.filterProvinsiId) {
                    params.set('provinsi_id', this.filterProvinsiId);
                }
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
                    const response = await fetch(`{{ route('master.plotting.index') }}?${this.buildParams()}`, {
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
            },
            openCreateModal() {
                this.editingId = null;
                this.form = { plot: '', provinsi_ids: [] };
                this.provinceQuery = '';
                this.errors = {};
                this.modalOpen = true;
            },
            openEditModal(row) {
                this.editingId = row.id;
                this.form = {
                    plot: row.plot,
                    provinsi_ids: Array.isArray(row.provinsi_ids) ? [...row.provinsi_ids].map(Number) : [],
                };
                this.provinceQuery = '';
                this.errors = {};
                this.modalOpen = true;
            },
            closeModal() {
                if (this.saving) return;
                this.modalOpen = false;
            },
            isProvinceSelected(id) {
                return this.form.provinsi_ids.includes(Number(id));
            },
            toggleProvince(id) {
                const numId = Number(id);
                if (this.isProvinceSelected(numId)) {
                    this.form.provinsi_ids = this.form.provinsi_ids.filter(item => item !== numId);
                } else {
                    this.form.provinsi_ids.push(numId);
                }
            },
            selectAllFilteredProvinces() {
                const ids = this.filteredModalProvinces.map(p => Number(p.id));
                const merged = new Set([...this.form.provinsi_ids, ...ids]);
                this.form.provinsi_ids = Array.from(merged);
            },
            clearSelectedProvinces() {
                this.form.provinsi_ids = [];
            },
            getProvinceById(id) {
                return this.provinces.find(p => Number(p.id) === Number(id));
            },
            async savePlotting() {
                this.saving = true;
                this.errors = {};
                const isEdit = Boolean(this.editingId);
                const url = isEdit
                    ? `{{ url('/master/plotting') }}/${this.editingId}`
                    : `{{ route('master.plotting.store') }}`;
                const method = isEdit ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(this.form),
                    });

                    const json = await response.json();

                    if (response.status === 422) {
                        this.errors = json.errors || {};
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(json.message || 'Terjadi kesalahan saat menyimpan data.');
                    }

                    this.modalOpen = false;
                    this.showFlash(json.message || 'Data plotting berhasil disimpan.');
                    this.fetchData();
                } catch (e) {
                    console.error(e);
                } finally {
                    this.saving = false;
                }
            },
            confirmDelete(row) {
                this.deletingItem = row;
                this.deleteModalOpen = true;
            },
            async deletePlotting() {
                if (!this.deletingItem) return;
                this.deleting = true;
                try {
                    const response = await fetch(`{{ url('/master/plotting') }}/${this.deletingItem.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const json = await response.json();
                    this.deleteModalOpen = false;
                    this.deletingItem = null;
                    this.showFlash(json.message || 'Data plotting berhasil dihapus.');
                    if (this.rows.length === 1 && this.page > 1) {
                        this.page--;
                    }
                    this.fetchData();
                } catch (e) {
                    console.error(e);
                } finally {
                    this.deleting = false;
                }
            }
        }"
    >
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold tracking-tight">Data Plotting Wilayah</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Kelola pembagian plot wilayah (misalnya WIL I, WIL II) beserta daftar provinsi yang termasuk di dalamnya.
                </p>
            </div>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                <x-lucide-plus class="h-4 w-4" />
                <span>Tambah Plotting</span>
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

        {{-- Card Table --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-neutral-900/10 dark:bg-neutral-950 dark:ring-white/10">
            {{-- Filter Bar --}}
            <div class="rounded-t-xl border-b border-neutral-200 bg-neutral-50/40 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                <div class="grid gap-4 md:grid-cols-3">
                    <x-searchable-select
                        label="Filter Provinsi"
                        placeholder="Semua Provinsi"
                        itemsExpr="filterProvinces"
                        valueModel="filterProvinsiId"
                        changeAction="onFilterChange()"
                    />
                </div>

                <div class="mt-3 flex justify-end" x-show="filterProvinsiId !== ''" x-cloak>
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
                        placeholder="Cari nama plot atau provinsi..."
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
                                        index === 0 ? 'w-16 text-center' : '',
                                        index === 1 ? 'w-48' : '',
                                        index === 3 ? 'w-40 text-center' : '',
                                        index === 4 ? 'w-32 text-center' : ''
                                    ]"
                                    x-on:click="toggleSort(index)"
                                >
                                    <div
                                        class="flex items-center justify-between gap-2"
                                        :class="(index === 0 || index === 3 || index === 4) ? 'justify-center' : ''"
                                    >
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
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                    Belum ada data plotting wilayah. Klik tombol <span class="font-medium text-neutral-800 dark:text-neutral-200">Tambah Plotting</span> untuk menambahkan plot baru.
                                </td>
                            </tr>
                        </template>

                        <template x-for="(row, rIndex) in rows" :key="row.id">
                            <tr
                                class="border-b border-neutral-200 transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                                :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                            >
                                <td class="border-r border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>
                                <td class="border-r border-neutral-200 px-4 py-3 font-semibold text-neutral-900 dark:border-neutral-800 dark:text-neutral-100" x-text="row.plot"></td>
                                <td class="border-r border-neutral-200 px-4 py-3 dark:border-neutral-800">
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="prov in (row.provinces_list || [])" :key="prov.id">
                                            <span class="inline-flex items-center rounded-md border border-neutral-200 bg-white px-2 py-0.5 text-xs font-medium text-neutral-700 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200" x-text="prov.name"></span>
                                        </template>
                                    </div>
                                </td>
                                <td class="border-r border-neutral-200 px-4 py-3 text-center dark:border-neutral-800">
                                    <span
                                        class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-medium text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200"
                                        x-text="`${row.provinces_count || 0} Provinsi`"
                                    ></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center justify-center gap-1">
                                        <button
                                            type="button"
                                            x-on:click="openEditModal(row)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                                            title="Edit Plotting"
                                        >
                                            <x-lucide-pencil class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="confirmDelete(row)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                            title="Hapus Plotting"
                                        >
                                            <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
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
                    {{-- Input Plot --}}
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

                    {{-- Multi Select Provinsi --}}
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

                        {{-- Selected Chips --}}
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

                        {{-- Search inside provinces --}}
                        <div class="relative">
                            <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
                            <input
                                type="search"
                                x-model="provinceQuery"
                                placeholder="Cari nama provinsi..."
                                class="block h-8 w-full rounded-md border border-neutral-200 bg-white pl-8 pr-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                            />
                        </div>

                        {{-- Province Checkbox List --}}
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
    </div>
</x-admin-layout>
