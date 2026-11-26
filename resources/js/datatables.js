import { createTipTapEditor } from './tiptap';

function getBaseDataTableMethods() {
    return {
        totalPages() {
            return Math.max(1, Math.ceil(this.filtered / this.perPage));
        },
        startEntry() {
            return this.filtered === 0 ? 0 : (this.page - 1) * this.perPage + 1;
        },
        endEntry() {
            return Math.min(this.page * this.perPage, this.filtered);
        },
        visiblePages() {
            const total = this.totalPages();
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
        showFlash(message, type = 'success') {
            window.notify.show(type, message);
        },
        csrfToken() {
            return document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
        },
        extraFilterParams() {
            return {};
        },
        buildParams() {
            this.draw++;
            const params = new URLSearchParams();
            params.set('draw', this.draw);
            params.set('start', (this.page - 1) * this.perPage);
            params.set('length', this.perPage);
            params.set('search[value]', this.search);
            params.set('search[regex]', 'false');

            const extras = this.extraFilterParams();
            Object.entries(extras).forEach(([key, value]) => {
                if (value !== '' && value !== null && value !== undefined) {
                    params.set(key, value);
                }
            });

            this.columns.forEach((col, i) => {
                params.set(`columns[${i}][data]`, col.data);
                params.set(`columns[${i}][name]`, col.name);
                params.set(`columns[${i}][searchable]`, col.searchable ? 'true' : 'false');
                params.set(`columns[${i}][orderable]`, col.sortable ? 'true' : 'false');
                params.set(`columns[${i}][search][value]`, '');
                params.set(`columns[${i}][search][regex]`, 'false');
            });
            if (this.sortCol !== null && this.sortCol !== undefined) {
                params.set('order[0][column]', this.sortCol);
                params.set('order[0][dir]', this.sortDir);
            }
            return params.toString();
        },
        async fetchData() {
            this.loading = true;
            const currentDraw = this.draw + 1;
            try {
                const response = await fetch(`${this.indexUrl}?${this.buildParams()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (Number(json.draw) === currentDraw) {
                    this.rows = json.data || [];
                    this.total = Number(json.recordsTotal || 0);
                    this.filtered = Number(json.recordsFiltered || 0);
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        toggleSort(index) {
            if (!this.columns[index]?.sortable) return;
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
            if (p === '...' || p < 1 || p > this.totalPages() || p === this.page) return;
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
    };
}

function getBaseDataTableState(config) {
    return {
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
        sortCol: config.defaultSortCol !== undefined ? config.defaultSortCol : null,
        sortDir: config.defaultSortDir || 'desc',
        flashMessage: '',
        columns: config.columns || [],
        indexUrl: config.indexUrl,
        storeUrl: config.storeUrl,
        baseUrl: config.baseUrl,
    };
}

function getTodayLocalDate() {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${now.getFullYear()}-${month}-${day}`;
}

/**
 * Method toolbar TipTap untuk komponen dengan banyak editor per tab.
 * Komponen pemakai harus menyediakan state `editors` dan `activeTab`.
 */
function getTipTapToolbarMethods() {
    return {
        getActiveEditor() {
            return this.editors[this.activeTab] || null;
        },
        isActive(name, opts = {}) {
            const editor = this.getActiveEditor();
            return editor ? editor.isActive(name, opts) : false;
        },
        toggleBold() {
            this.getActiveEditor()?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.getActiveEditor()?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.getActiveEditor()?.chain().focus().toggleUnderline().run();
        },
        toggleStrike() {
            this.getActiveEditor()?.chain().focus().toggleStrike().run();
        },
        toggleSubscript() {
            this.getActiveEditor()?.chain().focus().toggleSubscript().run();
        },
        toggleSuperscript() {
            this.getActiveEditor()?.chain().focus().toggleSuperscript().run();
        },
        setHeading(level) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (level === 0) {
                editor.chain().focus().setParagraph().run();
            } else {
                editor.chain().focus().toggleHeading({ level }).run();
            }
        },
        toggleBulletList() {
            this.getActiveEditor()?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.getActiveEditor()?.chain().focus().toggleOrderedList().run();
        },
        sinkListItem() {
            this.getActiveEditor()?.chain().focus().sinkListItem('listItem').run();
        },
        liftListItem() {
            this.getActiveEditor()?.chain().focus().liftListItem('listItem').run();
        },
        setTextAlign(alignment) {
            this.getActiveEditor()?.chain().focus().setTextAlign(alignment).run();
        },
        undo() {
            this.getActiveEditor()?.chain().focus().undo().run();
        },
        redo() {
            this.getActiveEditor()?.chain().focus().redo().run();
        },
        canUndo() {
            return Boolean(this.getActiveEditor()?.can().undo());
        },
        canRedo() {
            return Boolean(this.getActiveEditor()?.can().redo());
        },
        insertTable(rows = 3, cols = 3) {
            this.getActiveEditor()?.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
        },
        addColumnAfter() {
            this.getActiveEditor()?.chain().focus().addColumnAfter().run();
        },
        deleteColumn() {
            this.getActiveEditor()?.chain().focus().deleteColumn().run();
        },
        addRowAfter() {
            this.getActiveEditor()?.chain().focus().addRowAfter().run();
        },
        deleteRow() {
            this.getActiveEditor()?.chain().focus().deleteRow().run();
        },
        deleteTable() {
            this.getActiveEditor()?.chain().focus().deleteTable().run();
        },
        setLink() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const previousUrl = editor.getAttributes('link').href;
            const url = window.prompt('Masukkan URL Link:', previousUrl);
            if (url === null) return;
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertImage() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const url = window.prompt('Masukkan URL Gambar / Embed:');
            if (url) {
                editor.chain().focus().setImage({ src: url }).run();
            }
        },
        clearFormatting() {
            this.getActiveEditor()?.chain().focus().unsetAllMarks().clearNodes().run();
        },
        focusActiveEditor(e) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (e && e.target && e.target.closest && e.target.closest('.ProseMirror')) {
                return;
            }
            if (!editor.isFocused) {
                try {
                    editor.commands.focus('end');
                } catch (err) {
                    // Safe fallback
                }
            }
        },
    };
}

export function registerDataTables(Alpine) {
    Alpine.data('wilayahDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        provinceCode: '',
        cityCode: '',
        districtCode: '',
        provinces: config.provinces || [],
        cities: [],
        districts: [],
        modalOpen: false,
        deleteModalOpen: false,
        editingTarget: null,
        deletingItem: null,
        deleteError: '',
        modalCities: [],
        modalDistricts: [],
        form: {
            level: 'province',
            code: '',
            province_code: '',
            city_code: '',
            district_code: '',
            province_name: '',
            city_name: '',
            district_name: '',
            village_name: '',
        },
        errors: {},

        get parentCodePrefix() {
            if (this.form.level === 'city') return this.form.province_code || '';
            if (this.form.level === 'district') return this.form.city_code || '';
            if (this.form.level === 'village') return this.form.district_code || '';
            return '';
        },
        get codePlaceholder() {
            if (this.form.level === 'province') return 'Contoh: 11 (2 digit)';
            if (this.form.level === 'city') return `Contoh: ${this.form.province_code || '11'}01 (4 digit)`;
            if (this.form.level === 'district') return `Contoh: ${this.form.city_code || '1101'}01 (6 digit)`;
            return `Contoh: ${this.form.district_code || '110101'}2001 (10 digit)`;
        },
        extraFilterParams() {
            return {
                province_code: this.provinceCode,
                city_code: this.cityCode,
                district_code: this.districtCode,
            };
        },
        async fetchOptions(type, parent = '') {
            const params = new URLSearchParams({ options: type, parent });
            const response = await fetch(`${this.indexUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            return await response.json();
        },
        async refreshProvinces() {
            this.provinces = await this.fetchOptions('provinces');
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
        async openCreateModal() {
            this.editingTarget = null;
            this.errors = {};
            let defaultLevel = 'province';
            if (this.districtCode) {
                defaultLevel = 'village';
            } else if (this.cityCode) {
                defaultLevel = 'district';
            } else if (this.provinceCode) {
                defaultLevel = 'city';
            }

            this.form = {
                level: defaultLevel,
                code: '',
                province_code: this.provinceCode || '',
                city_code: this.cityCode || '',
                district_code: this.districtCode || '',
                province_name: '',
                city_name: '',
                district_name: '',
                village_name: '',
            };

            this.modalCities = this.provinceCode ? await this.fetchOptions('cities', this.provinceCode) : [];
            this.modalDistricts = this.cityCode ? await this.fetchOptions('districts', this.cityCode) : [];
            this.applyParentCodeToInput();
            this.modalOpen = true;
        },
        async openEditModal(row) {
            this.editingTarget = {
                level: row.level,
                code: row.raw_code,
            };
            this.errors = {};
            this.form = {
                level: row.level,
                code: row.raw_code,
                province_code: row.province_code || '',
                city_code: row.city_code || '',
                district_code: row.district_code || '',
                province_name: row.province_name || '',
                city_name: row.city_name || '',
                district_name: row.district_name || '',
                village_name: row.village_name || '',
            };
            this.modalCities = this.form.province_code
                ? await this.fetchOptions('cities', this.form.province_code)
                : [];
            this.modalDistricts = this.form.city_code
                ? await this.fetchOptions('districts', this.form.city_code)
                : [];
            this.modalOpen = true;
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
        },
        onModalLevelChange() {
            if (this.editingTarget) return;
            this.errors = {};
            if (this.form.level === 'province') {
                this.form.province_code = '';
                this.form.city_code = '';
                this.form.district_code = '';
            } else if (this.form.level === 'city') {
                this.form.city_code = '';
                this.form.district_code = '';
            } else if (this.form.level === 'district') {
                this.form.district_code = '';
            }
            this.applyParentCodeToInput();
        },
        async onModalProvinceChange() {
            this.form.city_code = '';
            this.form.district_code = '';
            this.modalCities = [];
            this.modalDistricts = [];
            if (this.form.province_code) {
                this.modalCities = await this.fetchOptions('cities', this.form.province_code);
            }
            this.applyParentCodeToInput();
        },
        async onModalCityChange() {
            this.form.district_code = '';
            this.modalDistricts = [];
            if (this.form.city_code) {
                this.modalDistricts = await this.fetchOptions('districts', this.form.city_code);
            }
            this.applyParentCodeToInput();
        },
        onModalDistrictChange() {
            this.applyParentCodeToInput();
        },
        applyParentCodeToInput() {
            if (this.editingTarget) return;
            this.form.code = this.parentCodePrefix;
        },
        async saveWilayah() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingTarget);
            const url = isEdit
                ? `${this.baseUrl}/${this.editingTarget.level}/${this.editingTarget.code}`
                : this.storeUrl;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                    throw new Error(json.message || 'Terjadi kesalahan saat menyimpan data wilayah.');
                }

                this.modalOpen = false;
                this.showFlash(json.message || 'Data wilayah berhasil disimpan.');
                if (this.form.level === 'province') {
                    await this.refreshProvinces();
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteWilayah();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async syncTim(row) {
            if (!row || this.syncingId === row.id) return;
            this.syncingId = row.id;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}/sync`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    this.showFlash(json.message || 'Jumlah anggota tim berhasil disinkronkan.');
                    this.fetchData();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.syncingId = null;
            }
        },
        async syncAllTims() {
            if (this.syncing) return;
            this.syncing = true;
            try {
                const response = await fetch(`${this.baseUrl}/sync`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    this.showFlash(json.message || 'Seluruh data anggota tim berhasil disinkronkan.');
                    this.fetchData();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.syncing = false;
            }
        },
        getDeletingLabel(row) {
            if (!row) return '';
            if (row.level === 'village') return `Desa/Kelurahan ${row.village_name} (${row.code})`;
            if (row.level === 'district') return `Kecamatan ${row.district_name} (${row.code})`;
            if (row.level === 'city') return `${row.city_name} (${row.code})`;
            return `Provinsi ${row.province_name} (${row.code})`;
        },
        async deleteWilayah() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';
            try {
                const response = await fetch(
                    `${this.baseUrl}/${this.deletingItem.level}/${this.deletingItem.raw_code}`,
                    {
                        method: 'DELETE',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );

                const json = await response.json();

                if (!response.ok) {
                    this.deleteError = json.message || 'Gagal menghapus data wilayah.';
                    return;
                }

                const deletedLevel = this.deletingItem.level;
                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Data wilayah berhasil dihapus.');
                if (deletedLevel === 'province') {
                    await this.refreshProvinces();
                }
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = this.deleteError || 'Terjadi kesalahan saat menghapus data.';
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('kriteriaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterBidang: '',
        bidangOptions: [
            { code: 'is_opsnal', name: 'Operasional (Opsnal)' },
            { code: 'is_logistik', name: 'Logistik' },
            { code: 'is_sdm', name: 'SDM' },
            { code: 'is_garkeu', name: 'Anggaran & Keuangan (Garkeu)' },
        ],
        modalOpen: false,
        editingId: null,
        form: {
            nama: '',
            is_opsnal: false,
            is_logistik: false,
            is_sdm: false,
            is_garkeu: false,
        },
        errors: {},

        extraFilterParams() {
            return {
                bidang: this.filterBidang,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterBidang = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        openCreateModal() {
            this.editingId = null;
            this.form = {
                nama: '',
                is_opsnal: this.filterBidang === 'is_opsnal',
                is_logistik: this.filterBidang === 'is_logistik',
                is_sdm: this.filterBidang === 'is_sdm',
                is_garkeu: this.filterBidang === 'is_garkeu',
            };
            this.errors = {};
            this.modalOpen = true;
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.form = {
                nama: row.nama,
                is_opsnal: Boolean(row.is_opsnal),
                is_logistik: Boolean(row.is_logistik),
                is_sdm: Boolean(row.is_sdm),
                is_garkeu: Boolean(row.is_garkeu),
            };
            this.errors = {};
            this.modalOpen = true;
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
        },
        async saveKriteria() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;

            try {
                const response = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                this.showFlash(json.message || 'Kriteria berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menyimpan data.');
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deleting = true;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (!response.ok) {
                    window.notify.error(json.message || 'Gagal menghapus data.');
                    return;
                }
                this.showFlash(json.message || 'Kriteria berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error('Terjadi kesalahan saat menghapus data.');
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('kodeDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        isSub: Boolean(config.isSub),
        requiresTemuan: Boolean(config.requiresTemuan),
        parents: config.parents || [],
        filterKodeId: '',
        filterKodeTemuanId: '',
        modalOpen: false,
        editingId: null,
        form: { kode_id: '', kode_temuan_id: '', kode: '', nama: '' },
        errors: {},

        extraFilterParams() {
            return {
                kode_id: this.filterKodeId,
                kode_temuan_id: this.filterKodeTemuanId,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterKodeId = '';
            this.filterKodeTemuanId = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        openCreateModal() {
            this.editingId = null;
            this.form = {
                kode_id: this.filterKodeId || '',
                kode_temuan_id: this.filterKodeTemuanId || '',
                kode: '',
                nama: '',
            };
            this.errors = {};
            this.modalOpen = true;
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.form = {
                kode_id: row.kode_id ? String(row.kode_id) : '',
                kode_temuan_id: row.kode_temuan_id ? String(row.kode_temuan_id) : '',
                kode: row.kode,
                nama: row.nama,
            };
            this.errors = {};
            this.modalOpen = true;
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
        },
        async saveKode() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;

            try {
                const response = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                this.showFlash(json.message || 'Data berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menyimpan data.');
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deleting = true;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (!response.ok) {
                    window.notify.error(json.message || 'Gagal menghapus data.');
                    return;
                }
                this.showFlash(json.message || 'Data berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error('Terjadi kesalahan saat menghapus data.');
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('plottingDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterProvinsiId: '',
        provinces: config.provinces || [],
        modalOpen: false,
        deleteModalOpen: false,
        editingId: null,
        deletingItem: null,
        provinceQuery: '',
        form: {
            plot: '',
            provinsi_ids: [],
        },
        errors: {},

        get filterProvinces() {
            return this.provinces.map((p) => ({
                code: String(p.id),
                name: p.name,
            }));
        },
        get filteredModalProvinces() {
            const q = this.provinceQuery.trim().toLowerCase();
            if (!q) return this.provinces;
            return this.provinces.filter((p) => p.name.toLowerCase().includes(q));
        },
        extraFilterParams() {
            return {
                provinsi_id: this.filterProvinsiId,
            };
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
                this.form.provinsi_ids = this.form.provinsi_ids.filter((item) => item !== numId);
            } else {
                this.form.provinsi_ids.push(numId);
            }
        },
        selectAllFilteredProvinces() {
            const ids = this.filteredModalProvinces.map((p) => Number(p.id));
            const merged = new Set([...this.form.provinsi_ids, ...ids]);
            this.form.provinsi_ids = Array.from(merged);
        },
        clearSelectedProvinces() {
            this.form.provinsi_ids = [];
        },
        getProvinceById(id) {
            return this.provinces.find((p) => Number(p.id) === Number(id));
        },
        async savePlotting() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deletePlotting();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async deletePlotting() {
            if (!this.deletingItem) return;
            this.deleting = true;
            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();
                if (!response.ok) {
                    this.deleteError = json.message || 'Gagal menghapus data plotting.';
                    return;
                }
                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Data plotting berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = this.deleteError || 'Terjadi kesalahan saat menghapus data.';
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('userDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        sortCol: null,
        sortDir: 'desc',
        filterRole: '',
        authRole: config.authRole || '',
        authPlottingId: config.authPlottingId ? String(config.authPlottingId) : '',
        authProvinceCode: config.authProvinceCode ? String(config.authProvinceCode) : '',
        canImpersonate: Boolean(config.canImpersonate),
        roles: config.roles || [],
        levels: config.levels || [],
        bidangs: config.bidangs || [],
        plottings: config.plottings || [],
        allProvinces: config.provinces || [],
        modalCities: [],
        modalDistricts: [],
        modalVillages: [],
        modalOpen: false,
        deleteModalOpen: false,
        resetPasswordModalOpen: false,
        impersonateModalOpen: false,
        editingId: null,
        deletingItem: null,
        resettingItem: null,
        impersonatingItem: null,
        resettingPassword: false,
        impersonating: false,
        deleteError: '',
        ttdFile: null,
        ttdPreviewUrl: null,
        existingTtdUrl: null,
        form: {
            name: '',
            username: '',
            email: '',
            phone: '',
            role: '',
            password: '',
            password_confirmation: '',
            level: '',
            plotting_id: '',
            province_code: '',
            city_code: '',
            district_code: '',
            village_code: '',
            bidang: '',
            jabatan: '',
        },
        errors: {},

        get roleOptions() {
            const adminRoles = ['Super Admin', 'Admin Nasional', 'Admin Wilayah'];
            return this.roles.map((r) => ({
                code: r,
                name: r,
                group: adminRoles.includes(r) ? 'Administratif' : 'Audit',
            }));
        },
        isAuditTeamRole(role = this.form.role) {
            return [
                'Auditor',
                'Ketua Tim',
                'Koordinator dan Pengendali',
                'Pengawas Tim',
            ].includes(role);
        },
        get availableLevels() {
            if (this.authRole === 'Admin Nasional' || this.form.role === 'Admin Nasional') {
                return ['Nasional'];
            }
            if (this.authRole === 'Admin Wilayah') {
                if (this.isAuditTeamRole()) {
                    return ['Wilayah'];
                }
                return this.levels.filter((l) => l !== 'Nasional' && l !== 'Wilayah');
            }
            if (this.form.role === 'Admin Wilayah') {
                return this.levels.filter((l) => l !== 'Nasional' && l !== 'Wilayah');
            }
            if (this.isAuditTeamRole()) {
                return ['Nasional', 'Wilayah'];
            }
            return this.levels.filter((l) => l !== 'Wilayah');
        },
        get levelOptions() {
            return this.availableLevels.map((l) => ({ code: l, name: l }));
        },
        get bidangOptions() {
            const items = this.bidangs.map((b) => ({ code: b, name: b }));
            if (!this.editingId) {
                return [{ code: 'SEMUA_BIDANG', name: 'Semua Bidang' }, ...items];
            }
            return items;
        },
        get plottingOptions() {
            return this.plottings.map((p) => ({ code: String(p.id), name: p.name }));
        },
        get modalProvinces() {
            return this.allProvinces;
        },
        needsRegionalScope() {
            return [
                'Admin Nasional',
                'Admin Wilayah',
                'Auditor',
                'Auditee',
                'Ketua Tim',
                'Koordinator dan Pengendali',
                'Pengawas Tim',
            ].includes(this.form.role);
        },
        isLevelReadonly() {
            return this.availableLevels.length === 1;
        },
        isPlottingReadonly() {
            return this.authRole === 'Admin Nasional' && Boolean(this.authPlottingId);
        },
        isProvinceReadonly() {
            return this.authRole === 'Admin Wilayah' && Boolean(this.authProvinceCode);
        },
        isAuditee() {
            return this.form.role === 'Auditee';
        },
        showPlottingField() {
            return this.needsRegionalScope() && this.form.level === 'Nasional';
        },
        showProvinceField() {
            return (
                this.needsRegionalScope() &&
                ['Wilayah', 'Provinsi', 'Kabupaten', 'Kecamatan', 'Desa'].includes(this.form.level)
            );
        },
        showCityField() {
            return (
                this.needsRegionalScope() &&
                ['Kabupaten', 'Kecamatan', 'Desa'].includes(this.form.level)
            );
        },
        showDistrictField() {
            return this.needsRegionalScope() && ['Kecamatan', 'Desa'].includes(this.form.level);
        },
        showVillageField() {
            return this.needsRegionalScope() && this.form.level === 'Desa';
        },
        extraFilterParams() {
            return {
                role: this.filterRole,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterRole = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        async fetchOptions(type, parent = '') {
            const params = new URLSearchParams({ options: type, parent });
            const response = await fetch(`${this.indexUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            return await response.json();
        },
        openCreateModal() {
            this.editingId = null;
            this.errors = {};
            this.ttdFile = null;
            this.ttdPreviewUrl = null;
            this.existingTtdUrl = null;
            this.modalCities = [];
            this.modalDistricts = [];
            this.modalVillages = [];
            this.form = {
                name: '',
                username: '',
                email: '',
                phone: '',
                role: '',
                password: '',
                password_confirmation: '',
                level: '',
                plotting_id: '',
                province_code: '',
                city_code: '',
                district_code: '',
                village_code: '',
                bidang: '',
                jabatan: '',
            };
            this.modalOpen = true;
        },
        async openEditModal(row) {
            this.editingId = row.id;
            this.errors = {};
            this.ttdFile = null;
            this.ttdPreviewUrl = null;
            this.existingTtdUrl = row.ttd_url || null;
            const role = row.role || 'Auditor';
            let level = row.level || '';
            if (this.authRole === 'Admin Nasional' || role === 'Admin Nasional') {
                level = 'Nasional';
            } else if (this.authRole === 'Admin Wilayah' && this.isAuditTeamRole(role)) {
                level = 'Wilayah';
            } else if (
                (this.authRole === 'Admin Wilayah' || role === 'Admin Wilayah') &&
                !['Provinsi', 'Kabupaten', 'Kecamatan', 'Desa'].includes(level)
            ) {
                level = 'Provinsi';
            } else if (this.isAuditTeamRole(role)) {
                if (!['Nasional', 'Wilayah'].includes(level)) {
                    level = row.province_code ? 'Wilayah' : 'Nasional';
                }
            } else if (!level) {
                level = 'Provinsi';
            }

            this.form = {
                name: row.name || '',
                username: row.username || '',
                email: row.email || '',
                phone: row.phone || '',
                role,
                password: '',
                password_confirmation: '',
                level,
                plotting_id: row.plotting_id ? String(row.plotting_id) : '',
                province_code: row.province_code || '',
                city_code: row.city_code || '',
                district_code: row.district_code || '',
                village_code: row.village_code || '',
                bidang: row.bidang || '',
                jabatan: row.jabatan || '',
            };

            this.modalCities =
                this.form.province_code && this.showCityField()
                    ? await this.fetchOptions('cities', this.form.province_code)
                    : [];
            this.modalDistricts =
                this.form.city_code && this.showDistrictField()
                    ? await this.fetchOptions('districts', this.form.city_code)
                    : [];
            this.modalVillages =
                this.form.district_code && this.showVillageField()
                    ? await this.fetchOptions('villages', this.form.district_code)
                    : [];

            this.modalOpen = true;
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
        },
        onRoleChange() {
            this.errors = {};
            if (this.needsRegionalScope()) {
                if (!this.availableLevels.includes(this.form.level)) {
                    this.form.level = this.availableLevels[0];
                }
                this.onLevelChange();
            } else {
                this.form.level = '';
                this.form.plotting_id = '';
                this.form.province_code = '';
                this.form.city_code = '';
                this.form.district_code = '';
                this.form.village_code = '';
            }
        },
        async onLevelChange() {
            if (this.form.level === 'Nasional') {
                this.form.province_code = '';
                this.form.city_code = '';
                this.form.district_code = '';
                this.form.village_code = '';
                this.modalCities = [];
                this.modalDistricts = [];
                this.modalVillages = [];
                if (this.authPlottingId) {
                    this.form.plotting_id = this.authPlottingId;
                }
            } else {
                this.form.plotting_id = '';
                if (this.authProvinceCode) {
                    this.form.province_code = this.authProvinceCode;
                }
                if (this.form.level === 'Wilayah' || this.form.level === 'Provinsi') {
                    this.form.city_code = '';
                    this.form.district_code = '';
                    this.form.village_code = '';
                    this.modalCities = [];
                    this.modalDistricts = [];
                    this.modalVillages = [];
                } else if (this.form.level === 'Kabupaten') {
                    this.form.district_code = '';
                    this.form.village_code = '';
                    this.modalDistricts = [];
                    this.modalVillages = [];
                    if (this.form.province_code && this.modalCities.length === 0) {
                        this.modalCities = await this.fetchOptions('cities', this.form.province_code);
                    }
                } else if (this.form.level === 'Kecamatan') {
                    this.form.village_code = '';
                    this.modalVillages = [];
                    if (this.form.province_code && this.modalCities.length === 0) {
                        this.modalCities = await this.fetchOptions('cities', this.form.province_code);
                    }
                } else if (this.form.level === 'Desa') {
                    if (this.form.province_code && this.modalCities.length === 0) {
                        this.modalCities = await this.fetchOptions('cities', this.form.province_code);
                    }
                }
            }
        },
        onPlottingChange() {
            this.form.province_code = '';
            this.form.city_code = '';
            this.form.district_code = '';
            this.form.village_code = '';
            this.modalCities = [];
            this.modalDistricts = [];
            this.modalVillages = [];
        },
        async onModalProvinceChange() {
            this.form.city_code = '';
            this.form.district_code = '';
            this.form.village_code = '';
            this.modalCities = [];
            this.modalDistricts = [];
            this.modalVillages = [];
            if (this.form.province_code && this.showCityField()) {
                this.modalCities = await this.fetchOptions('cities', this.form.province_code);
            }
        },
        async onModalCityChange() {
            this.form.district_code = '';
            this.form.village_code = '';
            this.modalDistricts = [];
            this.modalVillages = [];
            if (this.form.city_code) {
                this.modalDistricts = await this.fetchOptions('districts', this.form.city_code);
            }
        },
        async onModalDistrictChange() {
            this.form.village_code = '';
            this.modalVillages = [];
            if (this.form.district_code) {
                this.modalVillages = await this.fetchOptions('villages', this.form.district_code);
            }
        },
        onModalVillageChange() { },
        onTtdChange(event) {
            const file = event.target.files?.[0] || null;
            this.ttdFile = file;
            if (file) {
                this.ttdPreviewUrl = URL.createObjectURL(file);
            } else {
                this.ttdPreviewUrl = null;
            }
        },
        async saveUser() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;

            const formData = new FormData();
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            Object.entries(this.form).forEach(([key, val]) => {
                if (val !== null && val !== undefined && val !== '') {
                    formData.append(key, val);
                }
            });

            if (this.ttdFile) {
                formData.append('ttd', this.ttdFile);
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const json = await response.json();

                if (response.status === 422) {
                    this.errors = json.errors || {};
                    return;
                }

                if (!response.ok) {
                    throw new Error(json.message || 'Terjadi kesalahan saat menyimpan user.');
                }

                this.modalOpen = false;
                this.showFlash(json.message || 'Data user berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteUser();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async syncTim(row) {
            if (!row || this.syncingId === row.id) return;
            this.syncingId = row.id;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}/sync`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    this.showFlash(json.message || 'Jumlah anggota tim berhasil disinkronkan.');
                    this.fetchData();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.syncingId = null;
            }
        },
        async syncAllTims() {
            if (this.syncing) return;
            this.syncing = true;
            try {
                const response = await fetch(`${this.baseUrl}/sync`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    this.showFlash(json.message || 'Seluruh data anggota tim berhasil disinkronkan.');
                    this.fetchData();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.syncing = false;
            }
        },
        confirmResetPassword(row) {
            this.resettingItem = row;
            this.resetPasswordModalOpen = true;
        },
        async executeResetPassword() {
            if (!this.resettingItem) return;
            this.resettingPassword = true;
            try {
                const response = await fetch(`${this.baseUrl}/${this.resettingItem.id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                this.resetPasswordModalOpen = false;
                this.resettingItem = null;
                this.showFlash(json.message || 'Password berhasil direset menjadi Password123.');
            } catch (e) {
                console.error(e);
            } finally {
                this.resettingPassword = false;
            }
        },
        confirmImpersonate(row) {
            this.impersonatingItem = row;
            this.impersonateModalOpen = true;
        },
        async executeImpersonate() {
            if (!this.impersonatingItem) return;
            this.impersonating = true;
            try {
                const response = await fetch(`${this.baseUrl}/${this.impersonatingItem.id}/impersonate`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok && json.redirect) {
                    window.location.href = json.redirect;
                    return;
                }
                this.impersonateModalOpen = false;
                this.impersonatingItem = null;
                this.showFlash(json.message || 'Gagal melakukan impersonate.');
            } catch (e) {
                console.error(e);
            } finally {
                this.impersonating = false;
            }
        },
        async deleteUser() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';
            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    this.deleteError = json.message || 'Gagal menghapus user.';
                    return;
                }

                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Data user berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = this.deleteError || 'Terjadi kesalahan saat menghapus data.';
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('timDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterLingkup: '',
        authRole: config.authRole || '',
        authPlottingId: config.authPlottingId ? String(config.authPlottingId) : '',
        authProvinceCode: config.authProvinceCode ? String(config.authProvinceCode) : '',
        lingkups: config.lingkups || ['Nasional', 'Wilayah'],
        plottings: config.plottings || [],
        allProvinces: config.provinces || [],
        daltuOptions: config.daltuOptions || [],
        dalnisOptions: config.dalnisOptions || [],
        ketuaTimOptions: config.ketuaTimOptions || [],
        auditorCandidates: config.auditorCandidates || [],
        modalOpen: false,
        deleteModalOpen: false,
        editingId: null,
        deletingItem: null,
        deleteError: '',
        auditorQuery: '',
        syncing: false,
        syncingId: null,
        form: {
            nama_tim: '',
            lingkup: (config.lingkups && config.lingkups[0]) || 'Nasional',
            plotting_id: config.authPlottingId ? String(config.authPlottingId) : '',
            province_code: config.authProvinceCode ? String(config.authProvinceCode) : '',
            daltu_id: '',
            dalnis_id: '',
            ketua_tim_id: '',
            ketua_tim_2_id: '',
            auditor_ids: [],
        },
        errors: {},

        isLingkupReadonly() {
            return this.lingkups.length === 1;
        },
        isPlottingReadonly() {
            return this.authRole === 'Admin Nasional' && Boolean(this.authPlottingId);
        },
        isProvinceReadonly() {
            return this.authRole === 'Admin Wilayah' && Boolean(this.authProvinceCode);
        },
        matchesCurrentScope(user) {
            if (this.form.lingkup === 'Nasional') {
                if (!this.form.plotting_id) return true;
                return String(user.plotting_id || '') === String(this.form.plotting_id);
            }
            if (this.form.lingkup === 'Wilayah') {
                if (!this.form.province_code) return true;
                return String(user.province_code || '') === String(this.form.province_code);
            }
            return true;
        },
        get lingkupOptions() {
            return this.lingkups.map((l) => ({ code: l, name: l }));
        },
        get plottingOptions() {
            return this.plottings.map((p) => ({ code: String(p.id), name: p.name }));
        },
        get filteredDaltuOptions() {
            return this.daltuOptions.filter((u) => this.matchesCurrentScope(u));
        },
        get filteredDalnisOptions() {
            return this.dalnisOptions.filter((u) => this.matchesCurrentScope(u));
        },
        get scopedKetuaTimOptions() {
            return this.ketuaTimOptions.filter((u) => this.matchesCurrentScope(u));
        },
        get availableKetuaTim1Options() {
            const kt2 = String(this.form.ketua_tim_2_id || '');
            return this.scopedKetuaTimOptions.filter((u) => String(u.code) !== kt2);
        },
        get availableKetuaTim2Options() {
            const kt1 = String(this.form.ketua_tim_id || '');
            return this.scopedKetuaTimOptions.filter((u) => String(u.code) !== kt1);
        },
        get availableAuditorCandidates() {
            const excludedIds = [
                Number(this.form.ketua_tim_id || 0),
                Number(this.form.ketua_tim_2_id || 0),
            ].filter((id) => id > 0);

            return this.auditorCandidates.filter(
                (u) => this.matchesCurrentScope(u) && !excludedIds.includes(Number(u.id))
            );
        },
        get filteredModalAuditors() {
            const q = this.auditorQuery.trim().toLowerCase();
            const list = this.availableAuditorCandidates;
            if (!q) return list;
            return list.filter((u) => u.name.toLowerCase().includes(q));
        },
        extraFilterParams() {
            return {
                lingkup: this.filterLingkup,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterLingkup = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        openCreateModal() {
            this.editingId = null;
            this.errors = {};
            this.auditorQuery = '';
            const defaultLingkup = this.lingkups[0] || 'Nasional';
            this.form = {
                nama_tim: '',
                lingkup: defaultLingkup,
                plotting_id: defaultLingkup === 'Nasional' && this.authPlottingId ? this.authPlottingId : '',
                province_code: defaultLingkup === 'Wilayah' && this.authProvinceCode ? this.authProvinceCode : '',
                daltu_id: '',
                dalnis_id: '',
                ketua_tim_id: '',
                ketua_tim_2_id: '',
                auditor_ids: [],
            };
            this.modalOpen = true;
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.errors = {};
            this.auditorQuery = '';
            const lingkup = this.isLingkupReadonly() ? this.lingkups[0] : (row.lingkup || 'Nasional');
            this.form = {
                nama_tim: row.nama_tim || '',
                lingkup,
                plotting_id:
                    lingkup === 'Nasional'
                        ? (this.authPlottingId || (row.plotting_id ? String(row.plotting_id) : ''))
                        : '',
                province_code:
                    lingkup === 'Wilayah'
                        ? (this.authProvinceCode || row.province_code || '')
                        : '',
                daltu_id: row.daltu_id ? String(row.daltu_id) : '',
                dalnis_id: row.dalnis_id ? String(row.dalnis_id) : '',
                ketua_tim_id: row.ketua_tim_id ? String(row.ketua_tim_id) : '',
                ketua_tim_2_id: row.ketua_tim_2_id ? String(row.ketua_tim_2_id) : '',
                auditor_ids: Array.isArray(row.auditor_ids) ? [...row.auditor_ids].map(Number) : [],
            };
            this.onKetuaTimChange();
            this.modalOpen = true;
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
        },
        onLingkupChange() {
            if (this.form.lingkup === 'Nasional') {
                this.form.province_code = '';
                if (this.authPlottingId) {
                    this.form.plotting_id = this.authPlottingId;
                }
            } else {
                this.form.plotting_id = '';
                if (this.authProvinceCode) {
                    this.form.province_code = this.authProvinceCode;
                }
            }
            this.onScopeValueChange();
        },
        onScopeValueChange() {
            if (
                this.form.daltu_id &&
                !this.filteredDaltuOptions.some((u) => String(u.code) === String(this.form.daltu_id))
            ) {
                this.form.daltu_id = '';
            }
            if (
                this.form.dalnis_id &&
                !this.filteredDalnisOptions.some((u) => String(u.code) === String(this.form.dalnis_id))
            ) {
                this.form.dalnis_id = '';
            }
            if (
                this.form.ketua_tim_id &&
                !this.scopedKetuaTimOptions.some((u) => String(u.code) === String(this.form.ketua_tim_id))
            ) {
                this.form.ketua_tim_id = '';
            }
            if (
                this.form.ketua_tim_2_id &&
                !this.scopedKetuaTimOptions.some((u) => String(u.code) === String(this.form.ketua_tim_2_id))
            ) {
                this.form.ketua_tim_2_id = '';
            }
            const allowedAuditorIds = new Set(this.availableAuditorCandidates.map((u) => Number(u.id)));
            this.form.auditor_ids = this.form.auditor_ids.filter((id) => allowedAuditorIds.has(Number(id)));
        },
        onKetuaTimChange() {
            if (
                this.form.ketua_tim_id &&
                this.form.ketua_tim_2_id &&
                String(this.form.ketua_tim_id) === String(this.form.ketua_tim_2_id)
            ) {
                this.form.ketua_tim_2_id = '';
            }

            const excludedIds = [
                Number(this.form.ketua_tim_id || 0),
                Number(this.form.ketua_tim_2_id || 0),
            ].filter((id) => id > 0);

            if (excludedIds.length > 0) {
                this.form.auditor_ids = this.form.auditor_ids.filter(
                    (id) => !excludedIds.includes(Number(id))
                );
            }
        },
        isAuditorSelected(id) {
            return this.form.auditor_ids.includes(Number(id));
        },
        toggleAuditor(id) {
            const numId = Number(id);
            if (this.isAuditorSelected(numId)) {
                this.form.auditor_ids = this.form.auditor_ids.filter((item) => item !== numId);
            } else {
                this.form.auditor_ids.push(numId);
            }
        },
        selectAllFilteredAuditors() {
            const ids = this.filteredModalAuditors.map((u) => Number(u.id));
            const merged = new Set([...this.form.auditor_ids, ...ids]);
            this.form.auditor_ids = Array.from(merged);
        },
        clearSelectedAuditors() {
            this.form.auditor_ids = [];
        },
        getAuditorById(id) {
            return this.auditorCandidates.find((u) => Number(u.id) === Number(id));
        },
        async saveTim() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = isEdit ? 'PUT' : 'POST';

            const payload = {
                ...this.form,
                plotting_id: this.form.plotting_id ? Number(this.form.plotting_id) : null,
                province_code: this.form.province_code || null,
                daltu_id: this.form.daltu_id ? Number(this.form.daltu_id) : null,
                dalnis_id: this.form.dalnis_id ? Number(this.form.dalnis_id) : null,
                ketua_tim_id: this.form.ketua_tim_id ? Number(this.form.ketua_tim_id) : null,
                ketua_tim_2_id: this.form.ketua_tim_2_id ? Number(this.form.ketua_tim_2_id) : null,
                auditor_ids: this.form.auditor_ids.map(Number),
            };

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const json = await response.json();

                if (response.status === 422) {
                    this.errors = json.errors || {};
                    return;
                }

                if (!response.ok) {
                    throw new Error(json.message || 'Terjadi kesalahan saat menyimpan data tim.');
                }

                this.modalOpen = false;
                this.showFlash(json.message || 'Data tim berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteTim();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async syncTim(row) {
            if (!row || this.syncingId === row.id) return;
            this.syncingId = row.id;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}/sync`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    this.showFlash(json.message || 'Jumlah anggota tim berhasil disinkronkan.');
                    this.fetchData();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.syncingId = null;
            }
        },
        async syncAllTims() {
            if (this.syncing) return;
            this.syncing = true;
            try {
                const response = await fetch(`${this.baseUrl}/sync`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    this.showFlash(json.message || 'Seluruh data anggota tim berhasil disinkronkan.');
                    this.fetchData();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.syncing = false;
            }
        },
        async deleteTim() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';
            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    this.deleteError = json.message || 'Gagal menghapus tim.';
                    return;
                }

                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Data tim berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = this.deleteError || 'Terjadi kesalahan saat menghapus data.';
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('sprinDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        sortCol: config.defaultSortCol !== undefined ? config.defaultSortCol : null,
        sortDir: config.defaultSortDir || 'desc',
        filterTahap: '',
        scopeLabel: config.scopeLabel || '',
        scopeName: config.scopeName || '',
        tims: config.tims || [],
        tahaps: config.tahaps || ['1', '2', 'Khusus'],
        modalOpen: false,
        deleteModalOpen: false,
        reviewModalOpen: false,
        editingId: null,
        deletingItem: null,
        deleteError: '',
        reviewSprin: null,
        reviewLogs: [],
        loadingReviews: false,
        savingReview: false,
        reviewFormOpen: false,
        reviewForm: { status: 'Disetujui', catatan: '' },
        reviewErrors: {},
        form: {
            no_sprin: '',
            no_surat_pemberitahuan: '',
            tim_id: '',
            tahap: '',
            tgl_mulai: '',
            tgl_akhir: '',
            keterangan: '',
        },
        files: { file_surat_perintah: null, file_surat_pemberitahuan: null },
        existing: { perintah: '', pemberitahuan: '' },
        errors: {},

        get timOptions() {
            return this.tims;
        },
        get tahapOptions() {
            return this.tahaps.map((t) => ({ code: t, name: t === 'Khusus' ? 'Khusus' : `Tahap ${t}` }));
        },
        get selectedTim() {
            return this.tims.find((t) => String(t.id) === String(this.form.tim_id)) || null;
        },
        extraFilterParams() {
            return { tahap: this.filterTahap };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterTahap = '';
            this.search = '';
            this.sortCol = config.defaultSortCol !== undefined ? config.defaultSortCol : null;
            this.sortDir = config.defaultSortDir || 'desc';
            this.page = 1;
            this.fetchData();
        },
        formatDate(value) {
            if (!value) return '-';
            const str = String(value).trim();
            if (str.includes('/')) return str;
            const parts = str.split('-');
            if (parts.length === 3) {
                const [y, m, d] = parts;
                return `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${y}`;
            }
            return str;
        },
        init() {
            this.fetchData();
            this.$watch('form.tgl_mulai', (newVal) => {
                if (newVal) {
                    this.onTglMulaiChange(newVal);
                }
            });
        },
        onTimChange() {
            // Daltu, Dalnis, Ketua Tim, dan Anggota otomatis mengikuti tim terpilih.
        },
        onTglMulaiChange(customVal = null) {
            const raw = customVal || this.form.tgl_mulai;
            if (!raw) {
                this.form.tgl_akhir = '';
                return;
            }
            const parts = String(raw).split('-');
            if (parts.length !== 3) return;
            const y = Number(parts[0]);
            const m = Number(parts[1]);
            const d = Number(parts[2]);
            if (isNaN(y) || isNaN(m) || isNaN(d) || !y || !m || !d) return;

            const date = new Date(Date.UTC(y, m - 1, d + 14));
            const outY = date.getUTCFullYear();
            const outM = String(date.getUTCMonth() + 1).padStart(2, '0');
            const outD = String(date.getUTCDate()).padStart(2, '0');
            this.form.tgl_akhir = `${outY}-${outM}-${outD}`;
        },
        resetFiles() {
            this.files = { file_surat_perintah: null, file_surat_pemberitahuan: null };
            if (this.$refs.filePerintah) this.$refs.filePerintah.value = '';
            if (this.$refs.filePemberitahuan) this.$refs.filePemberitahuan.value = '';
        },
        openCreateModal() {
            this.editingId = null;
            this.errors = {};
            this.existing = { perintah: '', pemberitahuan: '' };
            this.form = {
                no_sprin: '',
                no_surat_pemberitahuan: '',
                tim_id: '',
                tahap: '',
                tgl_mulai: '',
                tgl_akhir: '',
                keterangan: '',
            };
            this.modalOpen = true;
            this.$nextTick(() => this.resetFiles());
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.errors = {};
            this.existing = {
                perintah: row.file_surat_perintah_name || '',
                pemberitahuan: row.file_surat_pemberitahuan_name || '',
            };
            this.form = {
                no_sprin: row.no_sprin || '',
                no_surat_pemberitahuan: row.no_surat_pemberitahuan || '',
                tim_id: row.tim_id ? String(row.tim_id) : '',
                tahap: row.tahap || '',
                tgl_mulai: row.tgl_mulai || '',
                tgl_akhir: row.tgl_akhir_value || '',
                keterangan: row.keterangan || '',
            };
            this.modalOpen = true;
            this.$nextTick(() => this.resetFiles());
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
        },
        async saveSprin() {
            this.saving = true;
            this.errors = {};
            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;

            const formData = new FormData();
            if (isEdit) {
                formData.append('_method', 'PUT');
            }
            Object.entries(this.form).forEach(([key, val]) => {
                if (val !== null && val !== undefined && val !== '') {
                    formData.append(key, val);
                }
            });
            Object.entries(this.files).forEach(([key, file]) => {
                if (file) {
                    formData.append(key, file);
                }
            });

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const json = await response.json();

                if (response.status === 422) {
                    this.errors = json.errors || {};
                    return;
                }

                if (!response.ok) {
                    throw new Error(json.message || 'Terjadi kesalahan saat menyimpan data Sprin.');
                }

                this.modalOpen = false;
                this.showFlash(json.message || 'Data Sprin berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteSprin();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async deleteSprin() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';
            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    this.deleteError = json.message || 'Gagal menghapus Sprin.';
                    return;
                }

                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Data Sprin berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = this.deleteError || 'Terjadi kesalahan saat menghapus data.';
            } finally {
                this.deleting = false;
            }
        },
        openReviewModal(row) {
            this.reviewSprin = { ...row };
            this.reviewLogs = [];
            this.reviewModalOpen = true;
            this.reviewFormOpen = false;
            this.reviewForm = { status: 'Disetujui', catatan: '' };
            this.reviewErrors = {};
            this.fetchReviews(row.id);
        },
        closeReviewModal() {
            if (this.savingReview) return;
            this.reviewModalOpen = false;
        },
        async fetchReviews(sprinId) {
            this.loadingReviews = true;
            try {
                const response = await fetch(`${this.baseUrl}/${sprinId}/reviews`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    if (json.sprin) {
                        this.reviewSprin = {
                            ...this.reviewSprin,
                            ...json.sprin,
                        };
                    }
                    this.reviewLogs = json.reviews || [];
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingReviews = false;
            }
        },
        async saveReview() {
            if (!this.reviewSprin || !this.reviewSprin.can_review) return;
            this.savingReview = true;
            this.reviewErrors = {};

            try {
                const response = await fetch(`${this.baseUrl}/${this.reviewSprin.id}/reviews`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.reviewForm),
                });

                const json = await response.json();

                if (response.status === 422) {
                    this.reviewErrors = json.errors || {};
                    return;
                }

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menyimpan reviu.');
                }

                if (json.review) {
                    this.reviewLogs.unshift(json.review);
                }
                if (this.reviewSprin) {
                    this.reviewSprin.latest_review_status = json.latest_status;
                    this.reviewSprin.approved_by_daltu_date = json.approved_by_daltu_date;
                    this.reviewSprin.latest_review_date = json.latest_review_date || json.review?.created_at;
                }

                this.reviewFormOpen = false;
                this.reviewForm = { status: 'Disetujui', catatan: '' };
                this.showFlash(json.message || 'Reviu berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.savingReview = false;
            }
        },
    }));

    Alpine.data('templatePkaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterLingkup: '',
        authRole: config.authRole || '',
        authPlottingId: config.authPlottingId ? String(config.authPlottingId) : '',
        authProvinceCode: config.authProvinceCode ? String(config.authProvinceCode) : '',
        scopeLabel: config.scopeLabel || '',
        scopeName: config.scopeName || '',
        lingkups: config.lingkups || ['Nasional', 'Wilayah'],
        plottings: config.plottings || [],
        provinces: config.provinces || [],
        modalOpen: false,
        previewModalOpen: false,
        previewData: null,
        loadingPreview: false,
        deleteModalOpen: false,
        duplicateModalOpen: false,
        reviewModalOpen: false,
        reviewType: 'dalnis',
        reviewPka: null,
        reviewLogs: [],
        loadingReviews: false,
        reviewFormOpen: false,
        savingReview: false,
        reviewForm: {
            status: 'Disetujui',
            catatan: '',
        },
        reviewErrors: {},
        editingId: null,
        deletingItem: null,
        duplicatingItem: null,
        deleteError: '',
        duplicateError: '',
        saving: false,
        savingSection: false,
        deleting: false,
        duplicating: false,
        activeTab: 'pendahuluan',
        editors: {},
        sections: [
            { key: 'pendahuluan', label: '1. Pendahuluan' },
            { key: 'tujuan', label: '2. Tujuan' },
            { key: 'ruang_lingkup', label: '3. Ruang Lingkup Audit' },
            { key: 'sasaran', label: '4. Sasaran Audit' },
            { key: 'waktu_pelaksanaan', label: '5. Waktu Pelaksanaan' },
            { key: 'periode', label: '6. Periode Audit' },
            { key: 'penutup', label: '7. Penutup' },
        ],
        form: {
            nama_template: '',
            lingkup: (config.lingkups && config.lingkups[0]) || 'Nasional',
            plotting_id: config.authPlottingId ? String(config.authPlottingId) : '',
            kode_wilayah: config.authProvinceCode ? String(config.authProvinceCode) : '',
            pendahuluan: '',
            tujuan: '',
            ruang_lingkup: '',
            sasaran: '',
            waktu_pelaksanaan: '',
            periode: '',
            penutup: '',
        },
        errors: {},

        isLingkupReadonly() {
            return this.lingkups.length === 1;
        },
        isPlottingReadonly() {
            return this.authRole === 'Admin Nasional' && Boolean(this.authPlottingId);
        },
        isProvinceReadonly() {
            return this.authRole === 'Admin Wilayah' && Boolean(this.authProvinceCode);
        },
        get lingkupOptions() {
            return this.lingkups.map((l) => ({ code: l, name: l }));
        },
        get plottingOptions() {
            return this.plottings.map((p) => ({ code: String(p.id), name: p.name }));
        },
        get provinceOptions() {
            return this.provinces.map((p) => ({ code: String(p.code), name: p.name }));
        },
        extraFilterParams() {
            return {
                lingkup: this.filterLingkup,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterLingkup = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        onLingkupChange() {
            if (this.form.lingkup === 'Nasional') {
                this.form.plotting_id = this.authPlottingId || '';
                this.form.kode_wilayah = '';
            } else if (this.form.lingkup === 'Wilayah') {
                this.form.kode_wilayah = this.authProvinceCode || '';
                this.form.plotting_id = '';
            }
        },

        // TipTap Active Editor & Toolbar Helpers
        getActiveEditor() {
            return this.editors[this.activeTab] || null;
        },
        isActive(name, opts = {}) {
            const editor = this.getActiveEditor();
            return editor ? editor.isActive(name, opts) : false;
        },
        toggleBold() {
            this.getActiveEditor()?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.getActiveEditor()?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.getActiveEditor()?.chain().focus().toggleUnderline().run();
        },
        toggleStrike() {
            this.getActiveEditor()?.chain().focus().toggleStrike().run();
        },
        toggleSubscript() {
            this.getActiveEditor()?.chain().focus().toggleSubscript().run();
        },
        toggleSuperscript() {
            this.getActiveEditor()?.chain().focus().toggleSuperscript().run();
        },
        setHeading(level) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (level === 0) {
                editor.chain().focus().setParagraph().run();
            } else {
                editor.chain().focus().toggleHeading({ level }).run();
            }
        },
        toggleBulletList() {
            this.getActiveEditor()?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.getActiveEditor()?.chain().focus().toggleOrderedList().run();
        },
        sinkListItem() {
            this.getActiveEditor()?.chain().focus().sinkListItem('listItem').run();
        },
        liftListItem() {
            this.getActiveEditor()?.chain().focus().liftListItem('listItem').run();
        },
        setTextAlign(alignment) {
            this.getActiveEditor()?.chain().focus().setTextAlign(alignment).run();
        },
        undo() {
            this.getActiveEditor()?.chain().focus().undo().run();
        },
        redo() {
            this.getActiveEditor()?.chain().focus().redo().run();
        },
        canUndo() {
            return Boolean(this.getActiveEditor()?.can().undo());
        },
        canRedo() {
            return Boolean(this.getActiveEditor()?.can().redo());
        },
        insertTable(rows = 3, cols = 3) {
            this.getActiveEditor()?.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
        },
        addColumnBefore() {
            this.getActiveEditor()?.chain().focus().addColumnBefore().run();
        },
        addColumnAfter() {
            this.getActiveEditor()?.chain().focus().addColumnAfter().run();
        },
        deleteColumn() {
            this.getActiveEditor()?.chain().focus().deleteColumn().run();
        },
        addRowBefore() {
            this.getActiveEditor()?.chain().focus().addRowBefore().run();
        },
        addRowAfter() {
            this.getActiveEditor()?.chain().focus().addRowAfter().run();
        },
        deleteRow() {
            this.getActiveEditor()?.chain().focus().deleteRow().run();
        },
        deleteTable() {
            this.getActiveEditor()?.chain().focus().deleteTable().run();
        },
        setLink() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const previousUrl = editor.getAttributes('link').href;
            const url = window.prompt('Masukkan URL Link:', previousUrl);
            if (url === null) return;
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertImage() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const url = window.prompt('Masukkan URL Gambar / Embed:');
            if (url) {
                editor.chain().focus().setImage({ src: url }).run();
            }
        },
        clearFormatting() {
            this.getActiveEditor()?.chain().focus().unsetAllMarks().clearNodes().run();
        },
        focusActiveEditor(e) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (e && e.target && e.target.closest && e.target.closest('.ProseMirror')) {
                return;
            }
            if (!editor.isFocused) {
                try {
                    editor.commands.focus('end');
                } catch (err) {
                    // Safe fallback
                }
            }
        },

        decodeHtml(html) {
            if (!html || typeof html !== 'string') return '';
            if (html.includes('&lt;') || html.includes('&gt;') || html.includes('&amp;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            }
            return html;
        },

        resetForm() {
            const defaultLingkup = (this.lingkups && this.lingkups[0]) || 'Nasional';
            this.form = {
                nama_template: '',
                lingkup: defaultLingkup,
                plotting_id: defaultLingkup === 'Nasional' && this.authPlottingId ? this.authPlottingId : '',
                kode_wilayah: defaultLingkup === 'Wilayah' && this.authProvinceCode ? this.authProvinceCode : '',
                pendahuluan: '',
                tujuan: '',
                ruang_lingkup: '',
                sasaran: '',
                waktu_pelaksanaan: '',
                periode: '',
                penutup: '',
            };
            this.errors = {};
        },

        initTabEditor(key) {
            const el = this.$refs['editor_' + key];
            if (!el) return;

            if (this.editors[key]) {
                return;
            }

            const text = this.decodeHtml(this.form[key] || '');
            this.editors[key] = createTipTapEditor({
                element: el,
                content: text,
                onUpdate: (html) => {
                    this.form[key] = html;
                },
            });
        },

        destroyEditors() {
            Object.values(this.editors).forEach((editor) => {
                try {
                    editor?.destroy();
                } catch (e) {
                    // Ignore
                }
            });
            this.editors = {};
            this.sections.forEach((sec) => {
                const el = this.$refs['editor_' + sec.key];
                if (el) {
                    el.innerHTML = '';
                }
            });
        },

        switchTab(key) {
            if (this.activeTab === key) return;
            // Sync current editor to form before switching
            if (this.editors[this.activeTab]) {
                this.form[this.activeTab] = this.editors[this.activeTab].getHTML();
            }
            this.activeTab = key;
            this.$nextTick(() => {
                this.initTabEditor(key);
                this.focusActiveEditor();
            });
        },

        openCreateModal() {
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
            this.activeTab = 'pendahuluan';
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initTabEditor('pendahuluan');
                this.focusActiveEditor();
            });
        },

        async openEditModal(row) {
            this.destroyEditors();
            this.editingId = row.id;
            this.errors = {};
            this.activeTab = 'pendahuluan';

            const lingkup = this.isLingkupReadonly() ? this.lingkups[0] : (row.lingkup || 'Nasional');
            this.form = {
                nama_template: row.nama_template || '',
                lingkup,
                plotting_id:
                    lingkup === 'Nasional'
                        ? (this.authPlottingId || (row.plotting_id ? String(row.plotting_id) : ''))
                        : '',
                kode_wilayah:
                    lingkup === 'Wilayah'
                        ? (this.authProvinceCode || (row.kode_wilayah ? String(row.kode_wilayah) : ''))
                        : '',
                pendahuluan: this.decodeHtml(row.pendahuluan || ''),
                tujuan: this.decodeHtml(row.tujuan || ''),
                ruang_lingkup: this.decodeHtml(row.ruang_lingkup || ''),
                sasaran: this.decodeHtml(row.sasaran || ''),
                waktu_pelaksanaan: this.decodeHtml(row.waktu_pelaksanaan || ''),
                periode: this.decodeHtml(row.periode || ''),
                penutup: this.decodeHtml(row.penutup || ''),
            };
            this.modalOpen = true;

            this.$nextTick(() => {
                this.initTabEditor('pendahuluan');
                this.focusActiveEditor();
            });

            // Fetch latest data from server to ensure fresh content
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}?t=${Date.now()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    if (this.editingId === row.id && this.modalOpen) {
                        this.form.nama_template = data.nama_template || '';
                        this.form.pendahuluan = this.decodeHtml(data.pendahuluan || '');
                        this.form.tujuan = this.decodeHtml(data.tujuan || '');
                        this.form.ruang_lingkup = this.decodeHtml(data.ruang_lingkup || '');
                        this.form.sasaran = this.decodeHtml(data.sasaran || '');
                        this.form.waktu_pelaksanaan = this.decodeHtml(data.waktu_pelaksanaan || '');
                        this.form.periode = this.decodeHtml(data.periode || '');
                        this.form.penutup = this.decodeHtml(data.penutup || '');

                        const activeEditor = this.editors[this.activeTab];
                        if (activeEditor && data[this.activeTab] !== undefined) {
                            const decoded = this.decodeHtml(data[this.activeTab] || '');
                            if (activeEditor.getHTML() !== decoded) {
                                activeEditor.commands.setContent(decoded);
                            }
                        }
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        closeModal() {
            if (this.saving || this.savingSection) return;
            this.modalOpen = false;
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
        },

        async openPreviewModal(row) {
            this.loadingPreview = true;
            this.previewModalOpen = true;
            this.previewData = null;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}?t=${Date.now()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();
                if (response.ok) {
                    this.previewData = data;
                } else {
                    throw new Error(data.message || 'Gagal memuat preview template');
                }
            } catch (e) {
                console.error(e);
                this.previewData = null;
            } finally {
                this.loadingPreview = false;
            }
        },

        closePreviewModal() {
            this.previewModalOpen = false;
        },

        saveCurrentTab() {
            const currentSection = this.sections.find((s) => s.key === this.activeTab);
            this.saveTemplate(currentSection ? currentSection.label : null);
        },

        async saveTemplate(sectionLabel = null) {
            if (sectionLabel) {
                this.savingSection = true;
            } else {
                this.saving = true;
            }
            this.errors = {};

            // Synchronize all currently active editor instances into form state
            Object.keys(this.editors).forEach((key) => {
                if (this.editors[key]) {
                    this.form[key] = this.editors[key].getHTML();
                }
            });

            const url = this.editingId ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                    throw new Error(json.message || 'Gagal menyimpan template.');
                }

                if (json.data && json.data.id) {
                    this.editingId = json.data.id;
                }

                const successMsg = sectionLabel
                    ? `Bagian "${sectionLabel}" berhasil disimpan.`
                    : (json.message || 'Template PKA berhasil disimpan.');

                if (!sectionLabel) {
                    this.modalOpen = false;
                    this.destroyEditors();
                    this.editingId = null;
                    this.resetForm();
                }
                this.showFlash(successMsg);
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = {
                    general: [e.message || 'Terjadi kesalahan sistem.'],
                };
            } finally {
                this.saving = false;
                this.savingSection = false;
            }
        },

        async confirmDuplicate(row) {
            const confirmed = await window.notify.confirm({
                title: 'Duplikasi template?',
                text: `Template "${row.nama_template || ''}" akan diduplikasi.`,
                confirmText: 'Ya, duplikasi',
            });
            if (!confirmed) return;
            this.duplicatingItem = row;
            this.duplicateError = '';
            await this.duplicateItem();
            if (this.duplicateError) {
                window.notify.error(this.duplicateError);
                this.duplicateError = '';
            }
        },

        closeDuplicateModal() {
            if (this.duplicating) return;
            this.duplicateModalOpen = false;
            this.duplicatingItem = null;
            this.duplicateError = '';
        },

        async duplicateItem() {
            if (!this.duplicatingItem) return;
            this.duplicating = true;
            this.duplicateError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.duplicatingItem.id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menduplikasi template.');
                }

                this.duplicateModalOpen = false;
                this.duplicatingItem = null;
                this.showFlash(json.message || 'Template PKA berhasil diduplikasi.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.duplicateError = e.message || 'Terjadi kesalahan saat menduplikasi template.';
            } finally {
                this.duplicating = false;
            }
        },

        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteItem();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },

        closeDeleteModal() {
            if (this.deleting) return;
            this.deleteModalOpen = false;
            this.deletingItem = null;
            this.deleteError = '';
        },

        async deleteItem() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus template.');
                }

                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Template PKA berhasil dihapus.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = e.message || 'Terjadi kesalahan saat menghapus template.';
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('pkaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterBidang: '',
        filterJenisAudit: '',
        filterStatus: '',
        sprins: config.sprins || [],
        templates: config.templates || [],
        bidangs: config.bidangs || ['Operasional', 'Logistik', 'Manajemen Anggaran dan Keuangan', 'Sumber Daya Manusia'],
        jenisAudits: config.jenisAudits || ['Audit Kinerja', 'Audit PNBP', 'Audit Tujuan Tertentu'],
        statuses: config.statuses || ['Draft', 'Final'],
        modalOpen: false,
        previewModalOpen: false,
        previewData: null,
        loadingPreview: false,
        deleteModalOpen: false,
        duplicateModalOpen: false,
        reviewModalOpen: false,
        reviewType: 'dalnis',
        reviewPka: null,
        reviewLogs: [],
        loadingReviews: false,
        reviewFormOpen: false,
        savingReview: false,
        reviewForm: {
            status: 'Disetujui',
            catatan: '',
        },
        reviewErrors: {},
        editingId: null,
        deletingItem: null,
        duplicatingItem: null,
        deleteError: '',
        duplicateError: '',
        duplicateNomorPka: '',
        saving: false,
        savingSection: false,
        deleting: false,
        duplicating: false,
        activeTab: 'pendahuluan',
        lampiranFile: null,
        existingLampiranName: '',
        existingLampiranUrl: '',
        editors: {},
        sections: [
            { key: 'pendahuluan', label: '10. Pendahuluan' },
            { key: 'tujuan', label: '11. Tujuan' },
            { key: 'ruang_lingkup', label: '12. Ruang Lingkup Audit' },
            { key: 'sasaran', label: '13. Sasaran Audit' },
            { key: 'waktu_pelaksanaan', label: '14. Waktu Pelaksanaan Auditi' },
            { key: 'periode', label: '15. Periode Audit' },
            { key: 'penutup', label: '16. Penutup' },
        ],
        form: {
            sprin_id: '',
            nomor_pka: '',
            tanggal: new Date().toISOString().split('T')[0],
            anggaran: '',
            bidang: '',
            jenis_audit: '',
            sasaran_auditi: '',
            dibuat_oleh_id: '',
            direviu_oleh_id: '',
            template_pka_id: '',
            status: 'Draft',
            pendahuluan: '',
            tujuan: '',
            ruang_lingkup: '',
            sasaran: '',
            waktu_pelaksanaan: '',
            periode: '',
            penutup: '',
        },
        errors: {},

        get selectedSprin() {
            if (!this.form.sprin_id) return null;
            return this.sprins.find((s) => String(s.id) === String(this.form.sprin_id)) || null;
        },

        get sprinMemberOptions() {
            const sprin = this.selectedSprin;
            if (!sprin || !Array.isArray(sprin.members)) return [];
            return sprin.members.map((m) => ({
                code: String(m.id),
                name: m.name,
                group: m.group || null,
            }));
        },

        onSprinChange() {
            const validIds = new Set(this.sprinMemberOptions.map((m) => String(m.code)));
            if (this.form.dibuat_oleh_id && !validIds.has(String(this.form.dibuat_oleh_id))) {
                this.form.dibuat_oleh_id = '';
            }
            if (this.form.direviu_oleh_id && !validIds.has(String(this.form.direviu_oleh_id))) {
                this.form.direviu_oleh_id = '';
            }
        },

        get sprinOptions() {
            return this.sprins.map((s) => ({
                code: String(s.id),
                name: s.label || s.no_sprin,
                group: s.tipe ? 'Lingkup: ' + s.tipe : null,
            }));
        },

        get templateOptions() {
            return this.templates.map((t) => ({
                code: String(t.id),
                name: t.nama_template + ' (' + t.lingkup + ')',
                group: t.lingkup ? 'Lingkup: ' + t.lingkup : null,
            }));
        },

        extraFilterParams() {
            return {
                bidang: this.filterBidang,
                jenis_audit: this.filterJenisAudit,
                status: this.filterStatus,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterBidang = '';
            this.filterJenisAudit = '';
            this.filterStatus = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },

        // TipTap Active Editor & Toolbar Helpers
        getActiveEditor() {
            return this.editors[this.activeTab] || null;
        },
        isActive(name, opts = {}) {
            const editor = this.getActiveEditor();
            return editor ? editor.isActive(name, opts) : false;
        },
        toggleBold() {
            this.getActiveEditor()?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.getActiveEditor()?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.getActiveEditor()?.chain().focus().toggleUnderline().run();
        },
        toggleStrike() {
            this.getActiveEditor()?.chain().focus().toggleStrike().run();
        },
        toggleSubscript() {
            this.getActiveEditor()?.chain().focus().toggleSubscript().run();
        },
        toggleSuperscript() {
            this.getActiveEditor()?.chain().focus().toggleSuperscript().run();
        },
        setHeading(level) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (level === 0) {
                editor.chain().focus().setParagraph().run();
            } else {
                editor.chain().focus().toggleHeading({ level }).run();
            }
        },
        toggleBulletList() {
            this.getActiveEditor()?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.getActiveEditor()?.chain().focus().toggleOrderedList().run();
        },
        sinkListItem() {
            this.getActiveEditor()?.chain().focus().sinkListItem('listItem').run();
        },
        liftListItem() {
            this.getActiveEditor()?.chain().focus().liftListItem('listItem').run();
        },
        setTextAlign(alignment) {
            this.getActiveEditor()?.chain().focus().setTextAlign(alignment).run();
        },
        undo() {
            this.getActiveEditor()?.chain().focus().undo().run();
        },
        redo() {
            this.getActiveEditor()?.chain().focus().redo().run();
        },
        canUndo() {
            return Boolean(this.getActiveEditor()?.can().undo());
        },
        canRedo() {
            return Boolean(this.getActiveEditor()?.can().redo());
        },
        insertTable(rows = 3, cols = 3) {
            this.getActiveEditor()?.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
        },
        addColumnBefore() {
            this.getActiveEditor()?.chain().focus().addColumnBefore().run();
        },
        addColumnAfter() {
            this.getActiveEditor()?.chain().focus().addColumnAfter().run();
        },
        deleteColumn() {
            this.getActiveEditor()?.chain().focus().deleteColumn().run();
        },
        addRowBefore() {
            this.getActiveEditor()?.chain().focus().addRowBefore().run();
        },
        addRowAfter() {
            this.getActiveEditor()?.chain().focus().addRowAfter().run();
        },
        deleteRow() {
            this.getActiveEditor()?.chain().focus().deleteRow().run();
        },
        deleteTable() {
            this.getActiveEditor()?.chain().focus().deleteTable().run();
        },
        setLink() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const previousUrl = editor.getAttributes('link').href;
            const url = window.prompt('Masukkan URL Link:', previousUrl);
            if (url === null) return;
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertImage() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const url = window.prompt('Masukkan URL Gambar / Embed:');
            if (url) {
                editor.chain().focus().setImage({ src: url }).run();
            }
        },
        clearFormatting() {
            this.getActiveEditor()?.chain().focus().unsetAllMarks().clearNodes().run();
        },
        focusActiveEditor(e) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (e && e.target && e.target.closest && e.target.closest('.ProseMirror')) {
                return;
            }
            if (!editor.isFocused) {
                try {
                    editor.commands.focus('end');
                } catch (err) {
                    // Safe fallback
                }
            }
        },

        decodeHtml(html) {
            if (!html || typeof html !== 'string') return '';
            if (html.includes('&lt;') || html.includes('&gt;') || html.includes('&amp;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            }
            return html;
        },

        resetForm() {
            this.form = {
                sprin_id: '',
                nomor_pka: '',
                tanggal: new Date().toISOString().split('T')[0],
                anggaran: '',
                bidang: '',
                jenis_audit: '',
                sasaran_auditi: '',
                dibuat_oleh_id: '',
                direviu_oleh_id: '',
                template_pka_id: '',
                status: 'Draft',
                pendahuluan: '',
                tujuan: '',
                ruang_lingkup: '',
                sasaran: '',
                waktu_pelaksanaan: '',
                periode: '',
                penutup: '',
            };
            this.lampiranFile = null;
            this.existingLampiranName = '';
            this.existingLampiranUrl = '';
            this.errors = {};
            const fileInput = document.getElementById('pka_lampiran_input');
            if (fileInput) fileInput.value = '';
        },

        onFileChange(e) {
            const files = e.target.files;
            this.lampiranFile = files && files.length > 0 ? files[0] : null;
        },

        async onTemplateChange(id = null) {
            const templateId = id || this.form.template_pka_id;
            if (!templateId) return;
            this.form.template_pka_id = String(templateId);

            let tpl = this.templates.find((t) => String(t.id) === String(templateId));
            if (!tpl || !tpl.pendahuluan) {
                try {
                    const res = await fetch(`/master/template/pka/${templateId}`, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (res.ok) {
                        tpl = await res.json();
                    }
                } catch (err) {
                    console.error(err);
                }
            }

            if (!tpl) return;

            this.destroyEditors();

            this.sections.forEach((sec) => {
                this.form[sec.key] = this.decodeHtml(tpl[sec.key] || '');
            });

            const name = (tpl.nama_template || '').toLowerCase();
            if (!this.form.jenis_audit) {
                if (name.includes('kinerja')) {
                    this.form.jenis_audit = 'Audit Kinerja';
                } else if (name.includes('pnbp')) {
                    this.form.jenis_audit = 'Audit PNBP';
                } else if (name.includes('khusus') || name.includes('tujuan tertentu') || name.includes('kepatuhan')) {
                    this.form.jenis_audit = 'Audit Tujuan Tertentu';
                }
            }
            if (!this.form.bidang) {
                if (name.includes('operasional')) {
                    this.form.bidang = 'Operasional';
                } else if (name.includes('logistik')) {
                    this.form.bidang = 'Logistik';
                } else if (name.includes('keuangan') || name.includes('anggaran')) {
                    this.form.bidang = 'Manajemen Anggaran dan Keuangan';
                } else if (name.includes('sdm') || name.includes('sumber daya')) {
                    this.form.bidang = 'Sumber Daya Manusia';
                }
            }

            this.$nextTick(() => {
                this.initTabEditor(this.activeTab);
                this.focusActiveEditor();
            });

            this.showFlash(`Format isian template "${tpl.nama_template}" berhasil dipasang.`);
        },

        initTabEditor(key) {
            const el = this.$refs['editor_' + key];
            if (!el) return;

            if (this.editors[key]) {
                return;
            }

            const text = this.decodeHtml(this.form[key] || '');
            this.editors[key] = createTipTapEditor({
                element: el,
                content: text,
                onUpdate: (html) => {
                    this.form[key] = html;
                },
            });
        },

        destroyEditors() {
            Object.values(this.editors).forEach((editor) => {
                try {
                    editor?.destroy();
                } catch (e) {
                    // Ignore
                }
            });
            this.editors = {};
            this.sections.forEach((sec) => {
                const el = this.$refs['editor_' + sec.key];
                if (el) {
                    el.innerHTML = '';
                }
            });
        },

        switchTab(key) {
            if (this.activeTab === key) return;
            if (this.editors[this.activeTab]) {
                this.form[this.activeTab] = this.editors[this.activeTab].getHTML();
            }
            this.activeTab = key;
            this.$nextTick(() => {
                this.initTabEditor(key);
                this.focusActiveEditor();
            });
        },

        openCreateModal() {
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
            this.activeTab = 'pendahuluan';
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initTabEditor('pendahuluan');
                this.focusActiveEditor();
            });
        },

        async openEditModal(row) {
            this.destroyEditors();
            this.editingId = row.id;
            this.resetForm();
            this.modalOpen = true;

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) {
                    throw new Error('Gagal memuat data PKA');
                }
                const data = await response.json();
                this.form = {
                    sprin_id: data.sprin_id ? String(data.sprin_id) : '',
                    nomor_pka: data.nomor_pka || '',
                    tanggal: data.tanggal || '',
                    anggaran: data.anggaran !== null && data.anggaran !== undefined && data.anggaran !== '' ? String(data.anggaran) : '0',
                    bidang: data.bidang || '',
                    jenis_audit: data.jenis_audit || '',
                    sasaran_auditi: data.sasaran_auditi || '',
                    dibuat_oleh_id: data.dibuat_oleh_id ? String(data.dibuat_oleh_id) : '',
                    direviu_oleh_id: data.direviu_oleh_id ? String(data.direviu_oleh_id) : '',
                    template_pka_id: data.template_pka_id ? String(data.template_pka_id) : '',
                    status: data.status || 'Draft',
                    pendahuluan: this.decodeHtml(data.pendahuluan || ''),
                    tujuan: this.decodeHtml(data.tujuan || ''),
                    ruang_lingkup: this.decodeHtml(data.ruang_lingkup || ''),
                    sasaran: this.decodeHtml(data.sasaran || ''),
                    waktu_pelaksanaan: this.decodeHtml(data.waktu_pelaksanaan || ''),
                    periode: this.decodeHtml(data.periode || ''),
                    penutup: this.decodeHtml(data.penutup || ''),
                };
                this.existingLampiranName = data.lampiran_name || '';
                this.existingLampiranUrl = data.lampiran_url || '';
                this.activeTab = 'pendahuluan';
                this.$nextTick(() => {
                    this.initTabEditor('pendahuluan');
                    this.focusActiveEditor();
                });
            } catch (e) {
                console.error(e);
                this.errors = { general: [e.message || 'Gagal memuat data PKA.'] };
            }
        },

        closeModal(force = false) {
            if (this.saving && !force) return;
            this.modalOpen = false;
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
        },

        async savePka() {
            this.saving = true;
            this.errors = {};

            Object.keys(this.editors).forEach((k) => {
                if (this.editors[k]) {
                    this.form[k] = this.editors[k].getHTML();
                }
            });

            const formData = new FormData();
            Object.keys(this.form).forEach((k) => {
                if (this.form[k] !== null && this.form[k] !== undefined) {
                    formData.append(k, this.form[k]);
                }
            });

            if (this.lampiranFile) {
                formData.append('lampiran', this.lampiranFile);
            }

            const isEdit = Boolean(this.editingId);
            const url = isEdit ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;

            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const json = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && json.errors) {
                        this.errors = json.errors;
                        const firstKey = Object.keys(json.errors)[0];
                        const firstMsg = json.errors[firstKey]?.[0] || 'Validasi formulir gagal.';
                        this.errors.general = [`Terdapat kesalahan pengisian: ${firstMsg}`];
                    } else {
                        this.errors = { general: [json.message || 'Terjadi kesalahan saat menyimpan data.'] };
                    }
                    this.$refs.modalScrollArea?.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                this.closeModal(true);
                this.showFlash(json.message || 'Data PKA berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = { general: [e.message || 'Terjadi kesalahan jaringan.'] };
                this.$refs.modalScrollArea?.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                this.saving = false;
            }
        },

        saveCurrentTab() {
            if (this.editors[this.activeTab]) {
                this.form[this.activeTab] = this.editors[this.activeTab].getHTML();
            }
            const currentSection = this.sections.find((s) => s.key === this.activeTab);
            this.showFlash(`Bagian ${currentSection ? currentSection.label : ''} tersimpan di formulir.`);
        },

        async openPreviewModal(row) {
            this.previewModalOpen = true;
            this.loadingPreview = true;
            this.previewData = null;

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (response.ok) {
                    this.previewData = await response.json();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingPreview = false;
            }
        },

        closePreviewModal() {
            this.previewModalOpen = false;
            this.previewData = null;
        },

        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteItem();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },

        closeDeleteModal() {
            if (this.deleting) return;
            this.deleteModalOpen = false;
            this.deletingItem = null;
            this.deleteError = '';
        },

        async deleteItem() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus data PKA.');
                }

                this.deleteModalOpen = false;
                this.deletingItem = null;
                this.showFlash(json.message || 'Program Kerja Audit (PKA) berhasil dihapus.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = e.message || 'Terjadi kesalahan saat menghapus data PKA.';
            } finally {
                this.deleting = false;
            }
        },

        async openDuplicateModal(row) {
            const nomorBaru = await window.notify.prompt({
                title: 'Duplikasi PKA',
                text: `Salin PKA ${row.nomor_pka} sebagai Draft baru.`,
                inputLabel: 'Nomor PKA baru',
                inputValue: `${row.nomor_pka} (Copy)`,
                confirmText: 'Duplikasi',
            });
            if (nomorBaru === null) return;
            this.duplicatingItem = row;
            this.duplicateNomorPka = nomorBaru;
            this.duplicateError = '';
            await this.submitDuplicate();
            if (this.duplicateError) {
                window.notify.error(this.duplicateError);
                this.duplicateError = '';
            }
        },

        closeDuplicateModal() {
            if (this.duplicating) return;
            this.duplicateModalOpen = false;
            this.duplicatingItem = null;
            this.duplicateError = '';
        },

        async submitDuplicate() {
            if (!this.duplicatingItem) return;
            this.duplicating = true;
            this.duplicateError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.duplicatingItem.id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        nomor_pka: this.duplicateNomorPka,
                    }),
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menduplikasi PKA.');
                }

                this.duplicateModalOpen = false;
                this.duplicatingItem = null;
                this.showFlash(json.message || 'Program Kerja Audit (PKA) berhasil diduplikasi.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.duplicateError = e.message || 'Terjadi kesalahan saat menduplikasi PKA.';
            } finally {
                this.duplicating = false;
            }
        },

        printPka(row) {
            const url = row?.print_url || `${this.baseUrl}/${row.id}/print`;
            window.open(url, '_blank');
        },

        openReviewDalnis(row) {
            this.openReviewModal(row, 'dalnis');
        },

        openReviewDaltu(row) {
            this.openReviewModal(row, 'daltu');
        },

        openReviewModal(row, type = 'dalnis') {
            this.reviewType = type;
            const isDalnis = type === 'dalnis';
            this.reviewPka = {
                ...row,
                review_type: type,
                role_label: isDalnis ? 'Pengawas Tim (Dalnis)' : 'Pengendali Mutu (Daltu)',
                can_review: isDalnis ? Boolean(row.can_review_dalnis) : Boolean(row.can_review_daltu),
                latest_review_status: isDalnis ? row.latest_dalnis_review_status : row.latest_daltu_review_status,
                latest_review_date: isDalnis ? row.latest_dalnis_review_date : row.latest_daltu_review_date,
            };
            this.reviewLogs = [];
            this.reviewModalOpen = true;
            this.reviewFormOpen = false;
            this.reviewForm = { status: 'Disetujui', catatan: '' };
            this.reviewErrors = {};
            this.fetchReviews(row.id, type);
        },

        closeReviewModal() {
            if (this.savingReview) return;
            this.reviewModalOpen = false;
        },

        async fetchReviews(pkaId, type) {
            this.loadingReviews = true;
            try {
                const response = await fetch(`${this.baseUrl}/${pkaId}/reviews/${type}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (response.ok) {
                    if (json.pka) {
                        this.reviewPka = {
                            ...this.reviewPka,
                            ...json.pka,
                        };
                    }
                    this.reviewLogs = json.reviews || [];
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingReviews = false;
            }
        },

        async saveReview() {
            if (!this.reviewPka || !this.reviewPka.can_review) return;
            this.savingReview = true;
            this.reviewErrors = {};

            try {
                const response = await fetch(`${this.baseUrl}/${this.reviewPka.id}/reviews/${this.reviewType}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.reviewForm),
                });

                const json = await response.json();

                if (response.status === 422) {
                    this.reviewErrors = json.errors || {};
                    return;
                }

                if (!response.ok) {
                    this.reviewErrors = { status: [json.message || 'Gagal menyimpan reviu.'] };
                    return;
                }

                if (json.review) {
                    this.reviewLogs.unshift(json.review);
                }
                if (this.reviewPka) {
                    this.reviewPka.latest_review_status = json.latest_status;
                    this.reviewPka.latest_review_date = json.latest_review_date;
                }

                this.reviewFormOpen = false;
                this.reviewForm = { status: 'Disetujui', catatan: '' };
                this.showFlash(json.message || 'Reviu berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.savingReview = false;
            }
        },

        async toggleRowStatus(row, targetStatus = null) {
            if (row.updatingStatus) return;
            const currentStatus = row.status || 'Draft';
            const newStatus = targetStatus ? targetStatus : (currentStatus === 'Final' ? 'Draft' : 'Final');
            if (targetStatus && targetStatus === currentStatus) return;

            const oldStatus = currentStatus;
            row.updatingStatus = true;
            row.status = newStatus;

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}/status`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                const json = await response.json();
                if (!response.ok) {
                    throw new Error(json.message || 'Gagal mengubah status PKA.');
                }

                this.showFlash(json.message || `Status PKA ${row.nomor_pka || ''} berhasil diubah menjadi ${newStatus}.`);
            } catch (e) {
                console.error(e);
                row.status = oldStatus;
                this.showFlash(e.message || 'Terjadi kesalahan saat mengubah status.', 'error');
            } finally {
                row.updatingStatus = false;
            }
        },
    }));

    Alpine.data('templateLangkahKerjaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterLingkup: '',
        authRole: config.authRole || '',
        authPlottingId: config.authPlottingId ? String(config.authPlottingId) : '',
        authProvinceCode: config.authProvinceCode ? String(config.authProvinceCode) : '',
        isPlottingLocked: Boolean(config.isPlottingLocked),
        isProvinceLocked: Boolean(config.isProvinceLocked),
        scopeLabel: config.scopeLabel || '',
        scopeName: config.scopeName || '',
        lingkups: config.lingkups || ['Nasional', 'Wilayah'],
        plottings: config.plottings || [],
        provinces: config.provinces || [],
        modalOpen: false,
        editingId: null,
        deletingItem: null,
        duplicatingItem: null,
        deleteError: '',
        duplicateError: '',
        saving: false,
        deleting: false,
        duplicating: false,
        form: {
            nama_template: '',
            lingkup: (config.lingkups && config.lingkups[0]) || 'Nasional',
            plotting_id: config.authPlottingId ? String(config.authPlottingId) : '',
            kode_wilayah: config.authProvinceCode ? String(config.authProvinceCode) : '',
        },
        errors: {},

        isLingkupReadonly() {
            return this.lingkups.length === 1;
        },
        isPlottingReadonly() {
            return this.isPlottingLocked || (this.authRole === 'Admin Nasional' && Boolean(this.authPlottingId));
        },
        isProvinceReadonly() {
            return this.isProvinceLocked || (this.authRole === 'Admin Wilayah' && Boolean(this.authProvinceCode));
        },
        get lingkupOptions() {
            return this.lingkups.map((l) => ({ code: l, name: l }));
        },
        get plottingOptions() {
            return (this.plottings || []).map((p) => ({ code: String(p.id), name: p.name }));
        },
        get provinceOptions() {
            return (this.provinces || []).map((p) => ({ code: String(p.code), name: p.name }));
        },
        extraFilterParams() {
            return {
                lingkup: this.filterLingkup,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterLingkup = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        onLingkupChange() {
            if (this.form.lingkup === 'Nasional') {
                this.form.plotting_id = this.authPlottingId || '';
                this.form.kode_wilayah = '';
            } else if (this.form.lingkup === 'Wilayah') {
                this.form.kode_wilayah = this.authProvinceCode || '';
                this.form.plotting_id = '';
            }
        },
        resetForm() {
            const defaultLingkup = (this.lingkups && this.lingkups[0]) || 'Nasional';
            this.form = {
                nama_template: '',
                lingkup: defaultLingkup,
                plotting_id: defaultLingkup === 'Nasional' && this.authPlottingId ? this.authPlottingId : '',
                kode_wilayah: defaultLingkup === 'Wilayah' && this.authProvinceCode ? this.authProvinceCode : '',
            };
            this.errors = {};
        },
        openCreateModal() {
            this.editingId = null;
            this.resetForm();
            this.modalOpen = true;
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.errors = {};
            const lingkup = this.isLingkupReadonly() ? this.lingkups[0] : (row.lingkup || 'Nasional');
            this.form = {
                nama_template: row.nama_template || '',
                lingkup,
                plotting_id:
                    lingkup === 'Nasional'
                        ? (this.authPlottingId || (row.plotting_id ? String(row.plotting_id) : ''))
                        : '',
                kode_wilayah:
                    lingkup === 'Wilayah'
                        ? (this.authProvinceCode || (row.kode_wilayah ? String(row.kode_wilayah) : ''))
                        : '',
            };
            this.modalOpen = true;
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
            this.editingId = null;
            this.resetForm();
        },
        async saveTemplate() {
            this.saving = true;
            this.errors = {};

            const url = this.editingId ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                    throw new Error(json.message || 'Gagal menyimpan template langkah kerja.');
                }

                this.modalOpen = false;
                this.editingId = null;
                this.resetForm();
                this.showFlash(json.message || 'Template Langkah Kerja berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = {
                    general: [e.message || 'Terjadi kesalahan sistem.'],
                };
            } finally {
                this.saving = false;
            }
        },
        async confirmDuplicate(row) {
            const confirmed = await window.notify.confirm({
                title: 'Duplikasi template?',
                text: `Template "${row.nama_template || ''}" akan diduplikasi.`,
                confirmText: 'Ya, duplikasi',
            });
            if (!confirmed) return;
            this.duplicatingItem = row;
            this.duplicateError = '';
            await this.duplicateItem();
            if (this.duplicateError) {
                window.notify.error(this.duplicateError);
                this.duplicateError = '';
            }
        },
        async duplicateItem() {
            if (!this.duplicatingItem) return;
            this.duplicating = true;
            this.duplicateError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.duplicatingItem.id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menduplikasi template.');
                }

                this.duplicatingItem = null;
                this.showFlash(json.message || 'Template Langkah Kerja berhasil diduplikasi.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.duplicateError = e.message || 'Terjadi kesalahan saat menduplikasi template.';
            } finally {
                this.duplicating = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteItem();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async deleteItem() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus template.');
                }

                this.deletingItem = null;
                this.showFlash(json.message || 'Template Langkah Kerja berhasil dihapus.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = e.message || 'Terjadi kesalahan saat menghapus template.';
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('templateLangkahKerjaDetailDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        templateId: config.templateId,
        namaTemplate: config.namaTemplate || '',
        lingkup: config.lingkup || '',
        scopeName: config.scopeName || '',
        jumlahLangkahKerja: Number(config.jumlahLangkahKerja || 0),
        backUrl: config.backUrl || '/master/template/langkah-kerja',
        bulkDeleteUrl: config.bulkDeleteUrl,
        selectedIds: [],
        bulkDeleting: false,
        modalOpen: false,
        editingId: null,
        deletingItem: null,
        deleteError: '',
        saving: false,
        deleting: false,
        editor: null,
        form: {
            uraian: '',
        },
        errors: {},

        isSelected(id) {
            return this.selectedIds.includes(Number(id));
        },
        toggleSelectRow(id) {
            const numId = Number(id);
            if (this.isSelected(numId)) {
                this.selectedIds = this.selectedIds.filter((item) => item !== numId);
            } else {
                this.selectedIds.push(numId);
            }
        },
        isAllPageSelected() {
            if (!this.rows || this.rows.length === 0) return false;
            return this.rows.every((r) => this.selectedIds.includes(Number(r.id)));
        },
        toggleSelectAllPage() {
            if (this.isAllPageSelected()) {
                const pageIds = this.rows.map((r) => Number(r.id));
                this.selectedIds = this.selectedIds.filter((id) => !pageIds.includes(id));
            } else {
                const pageIds = this.rows.map((r) => Number(r.id));
                const merged = new Set([...this.selectedIds, ...pageIds]);
                this.selectedIds = Array.from(merged);
            }
        },
        clearSelection() {
            this.selectedIds = [];
        },

        // TipTap Editor & Toolbar Helpers
        getActiveEditor() {
            return this.editor;
        },
        isActive(name, opts = {}) {
            return this.editor ? this.editor.isActive(name, opts) : false;
        },
        toggleBold() {
            this.editor?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.editor?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.editor?.chain().focus().toggleUnderline().run();
        },
        toggleStrike() {
            this.editor?.chain().focus().toggleStrike().run();
        },
        toggleSubscript() {
            this.editor?.chain().focus().toggleSubscript().run();
        },
        toggleSuperscript() {
            this.editor?.chain().focus().toggleSuperscript().run();
        },
        setHeading(level) {
            if (!this.editor) return;
            if (level === 0) {
                this.editor.chain().focus().setParagraph().run();
            } else {
                this.editor.chain().focus().toggleHeading({ level }).run();
            }
        },
        toggleBulletList() {
            this.editor?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.editor?.chain().focus().toggleOrderedList().run();
        },
        sinkListItem() {
            this.editor?.chain().focus().sinkListItem('listItem').run();
        },
        liftListItem() {
            this.editor?.chain().focus().liftListItem('listItem').run();
        },
        setTextAlign(alignment) {
            this.editor?.chain().focus().setTextAlign(alignment).run();
        },
        undo() {
            this.editor?.chain().focus().undo().run();
        },
        redo() {
            this.editor?.chain().focus().redo().run();
        },
        canUndo() {
            return Boolean(this.editor?.can().undo());
        },
        canRedo() {
            return Boolean(this.editor?.can().redo());
        },
        insertTable(rows = 3, cols = 3) {
            this.editor?.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
        },
        addColumnAfter() {
            this.editor?.chain().focus().addColumnAfter().run();
        },
        deleteColumn() {
            this.editor?.chain().focus().deleteColumn().run();
        },
        addRowAfter() {
            this.editor?.chain().focus().addRowAfter().run();
        },
        deleteRow() {
            this.editor?.chain().focus().deleteRow().run();
        },
        deleteTable() {
            this.editor?.chain().focus().deleteTable().run();
        },
        setLink() {
            if (!this.editor) return;
            const previousUrl = this.editor.getAttributes('link').href;
            const url = window.prompt('Masukkan URL Link:', previousUrl);
            if (url === null) return;
            if (url === '') {
                this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertImage() {
            if (!this.editor) return;
            const url = window.prompt('Masukkan URL Gambar / Embed:');
            if (url) {
                this.editor.chain().focus().setImage({ src: url }).run();
            }
        },
        clearFormatting() {
            this.editor?.chain().focus().unsetAllMarks().clearNodes().run();
        },
        focusEditor(e) {
            if (!this.editor) return;
            if (e && e.target && e.target.closest && e.target.closest('.ProseMirror')) {
                return;
            }
            if (!this.editor.isFocused) {
                try {
                    this.editor.commands.focus('end');
                } catch (err) {
                    // Safe fallback
                }
            }
        },
        decodeHtml(html) {
            if (!html || typeof html !== 'string') return '';
            if (html.includes('&lt;') || html.includes('&gt;') || html.includes('&amp;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            }
            return html;
        },
        initEditor() {
            const el = this.$refs.editor_uraian;
            if (!el) return;
            this.destroyEditor();
            const text = this.decodeHtml(this.form.uraian || '');
            this.editor = createTipTapEditor({
                element: el,
                content: text,
                onUpdate: (html) => {
                    this.form.uraian = html;
                },
            });
        },
        destroyEditor() {
            if (this.editor) {
                try {
                    this.editor.destroy();
                } catch (e) {
                    // Ignore
                }
                this.editor = null;
            }
            const el = this.$refs.editor_uraian;
            if (el) {
                el.innerHTML = '';
            }
        },
        openCreateModal() {
            this.editingId = null;
            this.form = { uraian: '' };
            this.errors = {};
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initEditor();
                this.focusEditor();
            });
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.form = {
                uraian: this.decodeHtml(row.uraian || ''),
            };
            this.errors = {};
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initEditor();
                this.focusEditor();
            });
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
            this.destroyEditor();
            this.editingId = null;
            this.form = { uraian: '' };
            this.errors = {};
        },
        async saveDetail() {
            this.saving = true;
            this.errors = {};

            if (this.editor) {
                this.form.uraian = this.editor.getHTML();
            }

            const url = this.editingId ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                    throw new Error(json.message || 'Gagal menyimpan uraian langkah kerja.');
                }

                if (json.jumlah_langkah_kerja !== undefined) {
                    this.jumlahLangkahKerja = Number(json.jumlah_langkah_kerja);
                }

                this.modalOpen = false;
                this.destroyEditor();
                this.editingId = null;
                this.form = { uraian: '' };
                this.showFlash(json.message || 'Uraian langkah kerja berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = {
                    general: [e.message || 'Terjadi kesalahan sistem.'],
                };
            } finally {
                this.saving = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({
                text: `Langkah kerja nomor ${row.DT_RowIndex || ''} akan dihapus.`,
            });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteItem();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async deleteItem() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus uraian langkah kerja.');
                }

                if (json.jumlah_langkah_kerja !== undefined) {
                    this.jumlahLangkahKerja = Number(json.jumlah_langkah_kerja);
                }

                this.selectedIds = this.selectedIds.filter((id) => id !== Number(this.deletingItem.id));
                this.deletingItem = null;
                this.showFlash(json.message || 'Uraian langkah kerja berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = e.message || 'Terjadi kesalahan saat menghapus uraian langkah kerja.';
            } finally {
                this.deleting = false;
            }
        },
        async confirmBulkDelete() {
            if (this.selectedIds.length === 0 || this.bulkDeleting) return;
            const count = this.selectedIds.length;
            const confirmed = await window.notify.confirmDelete({
                text: `${count} uraian langkah kerja yang dipilih akan dihapus.`,
            });
            if (!confirmed) return;

            this.bulkDeleting = true;
            try {
                const response = await fetch(this.bulkDeleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids: this.selectedIds }),
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus data terpilih.');
                }

                if (json.jumlah_langkah_kerja !== undefined) {
                    this.jumlahLangkahKerja = Number(json.jumlah_langkah_kerja);
                }

                this.selectedIds = [];
                this.showFlash(json.message || `${count} uraian langkah kerja berhasil dihapus.`);
                this.page = 1;
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menghapus data terpilih.');
            } finally {
                this.bulkDeleting = false;
            }
        },
    }));

    Alpine.data('pkaLangkahKerjaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        pkaId: config.pkaId,
        nomorPka: config.nomorPka || '',
        bidang: config.bidang || '',
        backUrl: config.backUrl || '/audit/pka',
        pkaShowUrl: config.pkaShowUrl,
        applyTemplateUrl: config.applyTemplateUrl,
        bulkDeleteUrl: config.bulkDeleteUrl,
        clearAllUrl: config.clearAllUrl,
        auditors: config.auditors || [],
        templates: config.templates || [],
        selectedTemplateId: '',
        applyingTemplate: false,

        get auditorOptions() {
            return (this.auditors || []).map((a) => ({
                code: String(a.id),
                name: a.name,
            }));
        },
        get templateOptions() {
            return (this.templates || []).map((t) => ({
                code: String(t.id),
                name: `${t.nama_template} (${t.jumlah_langkah_kerja} langkah)`,
            }));
        },
        selectedIds: [],
        bulkDeleting: false,
        clearing: false,
        modalOpen: false,
        editingId: null,
        deletingItem: null,
        deleteError: '',
        saving: false,
        deleting: false,
        editor: null,
        form: {
            uraian: '',
            dilaksanakan_rencana_id: '',
            dilaksanakan_realisasi_id: '',
            waktu_rencana: '',
            waktu_realisasi: '',
            catatan: '',
        },
        errors: {},

        isSelected(id) {
            return this.selectedIds.includes(Number(id));
        },
        toggleSelectRow(id) {
            const numId = Number(id);
            if (this.isSelected(numId)) {
                this.selectedIds = this.selectedIds.filter((item) => item !== numId);
            } else {
                this.selectedIds.push(numId);
            }
        },
        isAllPageSelected() {
            if (!this.rows || this.rows.length === 0) return false;
            return this.rows.every((r) => this.selectedIds.includes(Number(r.id)));
        },
        toggleSelectAllPage() {
            if (this.isAllPageSelected()) {
                const pageIds = this.rows.map((r) => Number(r.id));
                this.selectedIds = this.selectedIds.filter((id) => !pageIds.includes(id));
            } else {
                const pageIds = this.rows.map((r) => Number(r.id));
                const merged = new Set([...this.selectedIds, ...pageIds]);
                this.selectedIds = Array.from(merged);
            }
        },
        clearSelection() {
            this.selectedIds = [];
        },

        // TipTap Editor & Toolbar Helpers
        getActiveEditor() {
            return this.editor;
        },
        isActive(name, opts = {}) {
            return this.editor ? this.editor.isActive(name, opts) : false;
        },
        toggleBold() {
            this.editor?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.editor?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.editor?.chain().focus().toggleUnderline().run();
        },
        toggleStrike() {
            this.editor?.chain().focus().toggleStrike().run();
        },
        toggleSubscript() {
            this.editor?.chain().focus().toggleSubscript().run();
        },
        toggleSuperscript() {
            this.editor?.chain().focus().toggleSuperscript().run();
        },
        setHeading(level) {
            if (!this.editor) return;
            if (level === 0) {
                this.editor.chain().focus().setParagraph().run();
            } else {
                this.editor.chain().focus().toggleHeading({ level }).run();
            }
        },
        toggleBulletList() {
            this.editor?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.editor?.chain().focus().toggleOrderedList().run();
        },
        sinkListItem() {
            this.editor?.chain().focus().sinkListItem('listItem').run();
        },
        liftListItem() {
            this.editor?.chain().focus().liftListItem('listItem').run();
        },
        setTextAlign(alignment) {
            this.editor?.chain().focus().setTextAlign(alignment).run();
        },
        undo() {
            this.editor?.chain().focus().undo().run();
        },
        redo() {
            this.editor?.chain().focus().redo().run();
        },
        canUndo() {
            return Boolean(this.editor?.can().undo());
        },
        canRedo() {
            return Boolean(this.editor?.can().redo());
        },
        insertTable(rows = 3, cols = 3) {
            this.editor?.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
        },
        addColumnAfter() {
            this.editor?.chain().focus().addColumnAfter().run();
        },
        deleteColumn() {
            this.editor?.chain().focus().deleteColumn().run();
        },
        addRowAfter() {
            this.editor?.chain().focus().addRowAfter().run();
        },
        deleteRow() {
            this.editor?.chain().focus().deleteRow().run();
        },
        deleteTable() {
            this.editor?.chain().focus().deleteTable().run();
        },
        setLink() {
            if (!this.editor) return;
            const previousUrl = this.editor.getAttributes('link').href;
            const url = window.prompt('Masukkan URL Link:', previousUrl);
            if (url === null) return;
            if (url === '') {
                this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertImage() {
            if (!this.editor) return;
            const url = window.prompt('Masukkan URL Gambar / Embed:');
            if (url) {
                this.editor.chain().focus().setImage({ src: url }).run();
            }
        },
        clearFormatting() {
            this.editor?.chain().focus().unsetAllMarks().clearNodes().run();
        },
        focusEditor(e) {
            if (!this.editor) return;
            if (e && e.target && e.target.closest && e.target.closest('.ProseMirror')) {
                return;
            }
            if (!this.editor.isFocused) {
                try {
                    this.editor.commands.focus('end');
                } catch (err) {
                    // Safe fallback
                }
            }
        },
        decodeHtml(html) {
            if (!html || typeof html !== 'string') return '';
            if (html.includes('&lt;') || html.includes('&gt;') || html.includes('&amp;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            }
            return html;
        },
        initEditor() {
            const el = this.$refs.editor_uraian;
            if (!el) return;
            this.destroyEditor();
            const text = this.decodeHtml(this.form.uraian || '');
            this.editor = createTipTapEditor({
                element: el,
                content: text,
                onUpdate: (html) => {
                    this.form.uraian = html;
                },
            });
        },
        destroyEditor() {
            if (this.editor) {
                try {
                    this.editor.destroy();
                } catch (e) {
                    // Ignore
                }
                this.editor = null;
            }
            const el = this.$refs.editor_uraian;
            if (el) {
                el.innerHTML = '';
            }
        },
        resetForm() {
            this.form = {
                uraian: '',
                dilaksanakan_rencana_id: '',
                dilaksanakan_realisasi_id: '',
                waktu_rencana: '',
                waktu_realisasi: '',
                catatan: '',
            };
            this.errors = {};
        },
        openCreateModal() {
            this.editingId = null;
            this.resetForm();
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initEditor();
                this.focusEditor();
            });
        },
        openEditModal(row) {
            this.editingId = row.id;
            this.form = {
                uraian: this.decodeHtml(row.uraian || ''),
                dilaksanakan_rencana_id: row.dilaksanakan_rencana_id ? String(row.dilaksanakan_rencana_id) : '',
                dilaksanakan_realisasi_id: row.dilaksanakan_realisasi_id ? String(row.dilaksanakan_realisasi_id) : '',
                waktu_rencana: row.waktu_rencana !== null && row.waktu_rencana !== undefined ? String(row.waktu_rencana) : '',
                waktu_realisasi: row.waktu_realisasi !== null && row.waktu_realisasi !== undefined ? String(row.waktu_realisasi) : '',
                catatan: row.catatan || '',
            };
            this.errors = {};
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initEditor();
                this.focusEditor();
            });
        },
        closeModal() {
            if (this.saving) return;
            this.modalOpen = false;
            this.destroyEditor();
            this.editingId = null;
            this.resetForm();
        },
        async saveItem() {
            this.saving = true;
            this.errors = {};

            if (this.editor) {
                this.form.uraian = this.editor.getHTML();
            }

            const url = this.editingId ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                    throw new Error(json.message || 'Gagal menyimpan langkah kerja PKA.');
                }

                this.modalOpen = false;
                this.destroyEditor();
                this.editingId = null;
                this.resetForm();
                this.showFlash(json.message || 'Langkah Kerja PKA berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = {
                    general: [e.message || 'Terjadi kesalahan sistem.'],
                };
            } finally {
                this.saving = false;
            }
        },
        async applyTemplate() {
            if (!this.selectedTemplateId || this.applyingTemplate) return;

            const tpl = this.templates.find((t) => String(t.id) === String(this.selectedTemplateId));
            const confirmed = await window.notify.confirm({
                title: 'Gunakan Template Langkah Kerja?',
                text: `Seluruh langkah kerja dari template "${tpl?.nama_template || ''}" akan ditambahkan ke PKA ini.`,
                confirmText: 'Ya, Gunakan',
            });
            if (!confirmed) return;

            this.applyingTemplate = true;
            try {
                const response = await fetch(this.applyTemplateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        template_langkah_kerja_id: this.selectedTemplateId,
                    }),
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menerapkan template langkah kerja.');
                }

                this.selectedTemplateId = '';
                this.showFlash(json.message || 'Berhasil menerapkan template langkah kerja.');
                this.page = 1;
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menerapkan template.');
            } finally {
                this.applyingTemplate = false;
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({
                text: `Langkah kerja nomor ${row.DT_RowIndex || ''} akan dihapus.`,
            });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteItem();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },
        async deleteItem() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus langkah kerja PKA.');
                }

                this.selectedIds = this.selectedIds.filter((id) => id !== Number(this.deletingItem.id));
                this.deletingItem = null;
                this.showFlash(json.message || 'Langkah Kerja PKA berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = e.message || 'Terjadi kesalahan saat menghapus langkah kerja.';
            } finally {
                this.deleting = false;
            }
        },
        async confirmBulkDelete() {
            if (this.selectedIds.length === 0 || this.bulkDeleting) return;
            const count = this.selectedIds.length;
            const confirmed = await window.notify.confirmDelete({
                text: `${count} langkah kerja yang dipilih akan dihapus.`,
            });
            if (!confirmed) return;

            this.bulkDeleting = true;
            try {
                const response = await fetch(this.bulkDeleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids: this.selectedIds }),
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus data terpilih.');
                }

                this.selectedIds = [];
                this.showFlash(json.message || `${count} langkah kerja berhasil dihapus.`);
                this.page = 1;
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menghapus data terpilih.');
            } finally {
                this.bulkDeleting = false;
            }
        },
        async confirmClearAll() {
            if (this.clearing) return;
            const confirmed = await window.notify.confirmDelete({
                title: 'Kosongkan semua langkah kerja?',
                text: 'Seluruh langkah kerja pada PKA ini akan dikosongkan.',
                confirmText: 'Ya, Kosongkan',
            });
            if (!confirmed) return;

            this.clearing = true;
            try {
                const response = await fetch(this.clearAllUrl, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal mengosongkan langkah kerja.');
                }

                this.selectedIds = [];
                this.showFlash(json.message || 'Berhasil mengosongkan langkah kerja');
                this.page = 1;
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat mengosongkan langkah kerja.');
            } finally {
                this.clearing = false;
            }
        },
        printPka() {
            window.print();
        },
    }));

    Alpine.data('templateKkaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterLingkup: '',
        authRole: config.authRole || '',
        authPlottingId: config.authPlottingId ? String(config.authPlottingId) : '',
        authProvinceCode: config.authProvinceCode ? String(config.authProvinceCode) : '',
        isPlottingLocked: Boolean(config.isPlottingLocked),
        isProvinceLocked: Boolean(config.isProvinceLocked),
        scopeLabel: config.scopeLabel || '',
        scopeName: config.scopeName || '',
        lingkups: config.lingkups || ['Nasional', 'Wilayah'],
        plottings: config.plottings || [],
        provinces: config.provinces || [],
        modalOpen: false,
        previewModalOpen: false,
        previewData: null,
        loadingPreview: false,
        editingId: null,
        deletingItem: null,
        duplicatingItem: null,
        deleteError: '',
        duplicateError: '',
        saving: false,
        savingSection: false,
        deleting: false,
        duplicating: false,
        activeTab: 'tujuan',
        editors: {},
        sections: [
            { key: 'tujuan', label: '1. Tujuan' },
            { key: 'langkah', label: '2. Langkah' },
            { key: 'hasil', label: '3. Hasil' },
        ],
        form: {
            nama_template: '',
            lingkup: (config.lingkups && config.lingkups[0]) || 'Nasional',
            plotting_id: config.authPlottingId ? String(config.authPlottingId) : '',
            kode_wilayah: config.authProvinceCode ? String(config.authProvinceCode) : '',
            tujuan: '',
            langkah: '',
            hasil: '',
        },
        errors: {},

        isLingkupReadonly() {
            return this.lingkups.length === 1;
        },
        isPlottingReadonly() {
            return this.isPlottingLocked || (this.authRole === 'Admin Nasional' && Boolean(this.authPlottingId));
        },
        isProvinceReadonly() {
            return this.isProvinceLocked || (this.authRole === 'Admin Wilayah' && Boolean(this.authProvinceCode));
        },
        get lingkupOptions() {
            return this.lingkups.map((l) => ({ code: l, name: l }));
        },
        get plottingOptions() {
            return (this.plottings || []).map((p) => ({ code: String(p.id), name: p.name }));
        },
        get provinceOptions() {
            return (this.provinces || []).map((p) => ({ code: String(p.code), name: p.name }));
        },
        extraFilterParams() {
            return {
                lingkup: this.filterLingkup,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterLingkup = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },
        onLingkupChange() {
            if (this.form.lingkup === 'Nasional') {
                this.form.plotting_id = this.authPlottingId || '';
                this.form.kode_wilayah = '';
            } else if (this.form.lingkup === 'Wilayah') {
                this.form.kode_wilayah = this.authProvinceCode || '';
                this.form.plotting_id = '';
            }
        },

        // TipTap Active Editor & Toolbar Helpers
        getActiveEditor() {
            return this.editors[this.activeTab] || null;
        },
        isActive(name, opts = {}) {
            const editor = this.getActiveEditor();
            return editor ? editor.isActive(name, opts) : false;
        },
        toggleBold() {
            this.getActiveEditor()?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.getActiveEditor()?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.getActiveEditor()?.chain().focus().toggleUnderline().run();
        },
        toggleStrike() {
            this.getActiveEditor()?.chain().focus().toggleStrike().run();
        },
        toggleSubscript() {
            this.getActiveEditor()?.chain().focus().toggleSubscript().run();
        },
        toggleSuperscript() {
            this.getActiveEditor()?.chain().focus().toggleSuperscript().run();
        },
        setHeading(level) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (level === 0) {
                editor.chain().focus().setParagraph().run();
            } else {
                editor.chain().focus().toggleHeading({ level }).run();
            }
        },
        toggleBulletList() {
            this.getActiveEditor()?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.getActiveEditor()?.chain().focus().toggleOrderedList().run();
        },
        sinkListItem() {
            this.getActiveEditor()?.chain().focus().sinkListItem('listItem').run();
        },
        liftListItem() {
            this.getActiveEditor()?.chain().focus().liftListItem('listItem').run();
        },
        setTextAlign(alignment) {
            this.getActiveEditor()?.chain().focus().setTextAlign(alignment).run();
        },
        undo() {
            this.getActiveEditor()?.chain().focus().undo().run();
        },
        redo() {
            this.getActiveEditor()?.chain().focus().redo().run();
        },
        canUndo() {
            return Boolean(this.getActiveEditor()?.can().undo());
        },
        canRedo() {
            return Boolean(this.getActiveEditor()?.can().redo());
        },
        insertTable(rows = 3, cols = 3) {
            this.getActiveEditor()?.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
        },
        addColumnBefore() {
            this.getActiveEditor()?.chain().focus().addColumnBefore().run();
        },
        addColumnAfter() {
            this.getActiveEditor()?.chain().focus().addColumnAfter().run();
        },
        deleteColumn() {
            this.getActiveEditor()?.chain().focus().deleteColumn().run();
        },
        addRowBefore() {
            this.getActiveEditor()?.chain().focus().addRowBefore().run();
        },
        addRowAfter() {
            this.getActiveEditor()?.chain().focus().addRowAfter().run();
        },
        deleteRow() {
            this.getActiveEditor()?.chain().focus().deleteRow().run();
        },
        deleteTable() {
            this.getActiveEditor()?.chain().focus().deleteTable().run();
        },
        setLink() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const previousUrl = editor.getAttributes('link').href;
            const url = window.prompt('Masukkan URL Link:', previousUrl);
            if (url === null) return;
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertImage() {
            const editor = this.getActiveEditor();
            if (!editor) return;
            const url = window.prompt('Masukkan URL Gambar / Embed:');
            if (url) {
                editor.chain().focus().setImage({ src: url }).run();
            }
        },
        clearFormatting() {
            this.getActiveEditor()?.chain().focus().unsetAllMarks().clearNodes().run();
        },
        focusActiveEditor(e) {
            const editor = this.getActiveEditor();
            if (!editor) return;
            if (e && e.target && e.target.closest && e.target.closest('.ProseMirror')) {
                return;
            }
            if (!editor.isFocused) {
                try {
                    editor.commands.focus('end');
                } catch (err) {
                    // Safe fallback
                }
            }
        },

        decodeHtml(html) {
            if (!html || typeof html !== 'string') return '';
            if (html.includes('&lt;') || html.includes('&gt;') || html.includes('&amp;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            }
            return html;
        },

        resetForm() {
            const defaultLingkup = (this.lingkups && this.lingkups[0]) || 'Nasional';
            this.form = {
                nama_template: '',
                lingkup: defaultLingkup,
                plotting_id: defaultLingkup === 'Nasional' && this.authPlottingId ? this.authPlottingId : '',
                kode_wilayah: defaultLingkup === 'Wilayah' && this.authProvinceCode ? this.authProvinceCode : '',
                tujuan: '',
                langkah: '',
                hasil: '',
            };
            this.errors = {};
        },

        initTabEditor(key) {
            const el = this.$refs['editor_' + key];
            if (!el) return;

            if (this.editors[key]) {
                return;
            }

            const text = this.decodeHtml(this.form[key] || '');
            this.editors[key] = createTipTapEditor({
                element: el,
                content: text,
                onUpdate: (html) => {
                    this.form[key] = html;
                },
            });
        },

        destroyEditors() {
            Object.values(this.editors).forEach((editor) => {
                try {
                    editor?.destroy();
                } catch (e) {
                    // Ignore
                }
            });
            this.editors = {};
            this.sections.forEach((sec) => {
                const el = this.$refs['editor_' + sec.key];
                if (el) {
                    el.innerHTML = '';
                }
            });
        },

        switchTab(key) {
            if (this.activeTab === key) return;
            if (this.editors[this.activeTab]) {
                this.form[this.activeTab] = this.editors[this.activeTab].getHTML();
            }
            this.activeTab = key;
            this.$nextTick(() => {
                this.initTabEditor(key);
                this.focusActiveEditor();
            });
        },

        openCreateModal() {
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
            this.activeTab = 'tujuan';
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initTabEditor('tujuan');
                this.focusActiveEditor();
            });
        },

        async openEditModal(row) {
            this.destroyEditors();
            this.editingId = row.id;
            this.errors = {};
            this.activeTab = 'tujuan';

            const lingkup = this.isLingkupReadonly() ? this.lingkups[0] : (row.lingkup || 'Nasional');
            this.form = {
                nama_template: row.nama_template || '',
                lingkup,
                plotting_id:
                    lingkup === 'Nasional'
                        ? (this.authPlottingId || (row.plotting_id ? String(row.plotting_id) : ''))
                        : '',
                kode_wilayah:
                    lingkup === 'Wilayah'
                        ? (this.authProvinceCode || (row.kode_wilayah ? String(row.kode_wilayah) : ''))
                        : '',
                tujuan: this.decodeHtml(row.tujuan || ''),
                langkah: this.decodeHtml(row.langkah || ''),
                hasil: this.decodeHtml(row.hasil || ''),
            };
            this.modalOpen = true;

            this.$nextTick(() => {
                this.initTabEditor('tujuan');
                this.focusActiveEditor();
            });

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}?t=${Date.now()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    if (this.editingId === row.id && this.modalOpen) {
                        this.form.nama_template = data.nama_template || '';
                        this.form.tujuan = this.decodeHtml(data.tujuan || '');
                        this.form.langkah = this.decodeHtml(data.langkah || '');
                        this.form.hasil = this.decodeHtml(data.hasil || '');

                        const activeEditor = this.editors[this.activeTab];
                        if (activeEditor && data[this.activeTab] !== undefined) {
                            const decoded = this.decodeHtml(data[this.activeTab] || '');
                            if (activeEditor.getHTML() !== decoded) {
                                activeEditor.commands.setContent(decoded);
                            }
                        }
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        closeModal() {
            if (this.saving || this.savingSection) return;
            this.modalOpen = false;
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
        },

        async openPreviewModal(row) {
            this.loadingPreview = true;
            this.previewModalOpen = true;
            this.previewData = null;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}?t=${Date.now()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();
                if (response.ok) {
                    this.previewData = data;
                } else {
                    throw new Error(data.message || 'Gagal memuat preview template');
                }
            } catch (e) {
                console.error(e);
                this.previewData = null;
            } finally {
                this.loadingPreview = false;
            }
        },

        closePreviewModal() {
            this.previewModalOpen = false;
        },

        saveCurrentTab() {
            const currentSection = this.sections.find((s) => s.key === this.activeTab);
            this.saveTemplate(currentSection ? currentSection.label : null);
        },

        async saveTemplate(sectionLabel = null) {
            if (sectionLabel) {
                this.savingSection = true;
            } else {
                this.saving = true;
            }
            this.errors = {};

            Object.keys(this.editors).forEach((key) => {
                if (this.editors[key]) {
                    this.form[key] = this.editors[key].getHTML();
                }
            });

            const url = this.editingId ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
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
                    throw new Error(json.message || 'Gagal menyimpan template.');
                }

                if (json.data && json.data.id) {
                    this.editingId = json.data.id;
                }

                const successMsg = sectionLabel
                    ? `Bagian "${sectionLabel}" berhasil disimpan.`
                    : (json.message || 'Template KKA berhasil disimpan.');

                if (!sectionLabel) {
                    this.modalOpen = false;
                    this.destroyEditors();
                    this.editingId = null;
                    this.resetForm();
                }
                this.showFlash(successMsg);
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = {
                    general: [e.message || 'Terjadi kesalahan sistem.'],
                };
            } finally {
                this.saving = false;
                this.savingSection = false;
            }
        },

        async confirmDuplicate(row) {
            const confirmed = await window.notify.confirm({
                title: 'Duplikasi template?',
                text: `Template "${row.nama_template || ''}" akan diduplikasi.`,
                confirmText: 'Ya, duplikasi',
            });
            if (!confirmed) return;
            this.duplicatingItem = row;
            this.duplicateError = '';
            await this.duplicateItem();
            if (this.duplicateError) {
                window.notify.error(this.duplicateError);
                this.duplicateError = '';
            }
        },

        async duplicateItem() {
            if (!this.duplicatingItem) return;
            this.duplicating = true;
            this.duplicateError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.duplicatingItem.id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menduplikasi template.');
                }

                this.duplicatingItem = null;
                this.showFlash(json.message || 'Template KKA berhasil diduplikasi.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.duplicateError = e.message || 'Terjadi kesalahan saat menduplikasi template.';
            } finally {
                this.duplicating = false;
            }
        },

        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;
            this.deletingItem = row;
            this.deleteError = '';
            await this.deleteItem();
            if (this.deleteError) {
                window.notify.error(this.deleteError);
                this.deleteError = '';
            }
        },

        async deleteItem() {
            if (!this.deletingItem) return;
            this.deleting = true;
            this.deleteError = '';

            try {
                const response = await fetch(`${this.baseUrl}/${this.deletingItem.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus template.');
                }

                this.deletingItem = null;
                this.showFlash(json.message || 'Template KKA berhasil dihapus.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.deleteError = e.message || 'Terjadi kesalahan saat menghapus template.';
            } finally {
                this.deleting = false;
            }
        },
    }));
    Alpine.data('kkaDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        ...getTipTapToolbarMethods(),
        filterBidang: '',
        filterJenisAudit: '',
        filterStatus: '',
        pkas: config.pkas || [],
        templates: config.templates || [],
        plottings: config.plottings || [],
        provinces: config.provinces || [],
        bidangs: config.bidangs || [],
        jenisAudits: config.jenisAudits || [],
        statuses: config.statuses || ['Draft', 'Final'],
        levels: config.levels || ['Nasional', 'Wilayah'],
        cities: [],
        districts: [],
        villages: [],
        reviewTypes: [
            { key: 'katim', label: 'Katim' },
            { key: 'dalnis', label: 'Dalnis' },
            { key: 'daltu', label: 'Daltu' },
        ],
        modalOpen: false,
        editingId: null,
        savingSection: false,
        activeTab: 'tujuan',
        editors: {},
        sections: [
            { key: 'tujuan', label: '9. Tujuan' },
            { key: 'langkah', label: '10. Langkah' },
            { key: 'hasil', label: '11. Hasil' },
        ],
        reviewModalOpen: false,
        reviewType: 'katim',
        reviewKka: null,
        reviewLogs: [],
        loadingReviews: false,
        reviewFormOpen: false,
        savingReview: false,
        reviewForm: {
            status: 'Disetujui',
            catatan: '',
        },
        reviewErrors: {},
        form: {
            nomor_kka: '',
            pka_id: '',
            tanggal_dibuat: getTodayLocalDate(),
            tanggal_direviu: getTodayLocalDate(),
            tanggal_kka: getTodayLocalDate(),
            tempat: '',
            auditee_level: 'Nasional',
            auditee_plotting_id: '',
            province_code: '',
            city_code: '',
            district_code: '',
            village_code: '',
            template_kka_id: '',
            tujuan: '',
            langkah: '',
            hasil: '',
            status: 'Draft',
        },
        errors: {},

        get selectedPka() {
            if (!this.form.pka_id) return null;
            return this.pkas.find((p) => String(p.id) === String(this.form.pka_id)) || null;
        },
        get pkaOptions() {
            return this.pkas.map((p) => ({
                code: String(p.id),
                name: p.name,
                group: p.bidang ? 'Bidang: ' + p.bidang : null,
            }));
        },
        get templateOptions() {
            return this.templates.map((t) => ({
                code: String(t.id),
                name: t.nama_template + ' (' + t.lingkup + ')',
                group: t.lingkup ? 'Lingkup: ' + t.lingkup : null,
            }));
        },
        get plottingOptions() {
            return this.plottings.map((p) => ({ code: String(p.id), name: p.name }));
        },
        get provinceOptions() {
            return this.provinces.map((p) => ({ code: String(p.code), name: p.name }));
        },
        get cityOptions() {
            return this.cities.map((c) => ({ code: String(c.code), name: c.name }));
        },
        get districtOptions() {
            return this.districts.map((d) => ({ code: String(d.code), name: d.name }));
        },
        get villageOptions() {
            return this.villages.map((v) => ({ code: String(v.code), name: v.name }));
        },

        extraFilterParams() {
            return {
                bidang: this.filterBidang,
                jenis_audit: this.filterJenisAudit,
                status: this.filterStatus,
            };
        },
        onFilterChange() {
            this.page = 1;
            this.fetchData();
        },
        resetFilters() {
            this.filterBidang = '';
            this.filterJenisAudit = '';
            this.filterStatus = '';
            this.search = '';
            this.page = 1;
            this.fetchData();
        },

        decodeHtml(html) {
            if (!html || typeof html !== 'string') return '';
            if (html.includes('&lt;') || html.includes('&gt;') || html.includes('&amp;')) {
                const txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            }
            return html;
        },

        // Auditee (cascading wilayah)
        async fetchOptions(type, parent = '') {
            const params = new URLSearchParams({ options: type, parent });
            const response = await fetch(`${this.indexUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            return await response.json();
        },
        resetRegionOptions() {
            this.cities = [];
            this.districts = [];
            this.villages = [];
        },
        onLevelChange() {
            this.form.auditee_plotting_id = '';
            this.form.province_code = '';
            this.form.city_code = '';
            this.form.district_code = '';
            this.form.village_code = '';
            this.resetRegionOptions();
        },
        async onProvinceChange() {
            this.form.city_code = '';
            this.form.district_code = '';
            this.form.village_code = '';
            this.districts = [];
            this.villages = [];
            this.cities = this.form.province_code ? await this.fetchOptions('cities', this.form.province_code) : [];
        },
        async onCityChange() {
            this.form.district_code = '';
            this.form.village_code = '';
            this.villages = [];
            this.districts = this.form.city_code ? await this.fetchOptions('districts', this.form.city_code) : [];
        },
        async onDistrictChange() {
            this.form.village_code = '';
            this.villages = this.form.district_code ? await this.fetchOptions('villages', this.form.district_code) : [];
        },
        onPkaChange() {
            this.errors = { ...this.errors, pka_id: undefined };
        },

        // Template KKA -> Tujuan, Langkah, Hasil
        async onTemplateChange() {
            const templateId = this.form.template_kka_id;
            if (!templateId) return;

            let tpl = this.templates.find((t) => String(t.id) === String(templateId));
            if (!tpl) {
                try {
                    const res = await fetch(`/master/template/kka/${templateId}`, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (res.ok) {
                        tpl = await res.json();
                    }
                } catch (err) {
                    console.error(err);
                }
            }

            if (!tpl) return;

            this.destroyEditors();
            this.sections.forEach((sec) => {
                this.form[sec.key] = this.decodeHtml(tpl[sec.key] || '');
            });

            this.$nextTick(() => {
                this.initTabEditor(this.activeTab);
                this.focusActiveEditor();
            });

            this.showFlash(`Tujuan, langkah, dan hasil dari template "${tpl.nama_template}" berhasil dipasang.`);
        },

        // Editor TipTap per tab
        initTabEditor(key) {
            const el = this.$refs['editor_' + key];
            if (!el || this.editors[key]) return;

            this.editors[key] = createTipTapEditor({
                element: el,
                content: this.decodeHtml(this.form[key] || ''),
                onUpdate: (html) => {
                    this.form[key] = html;
                },
            });
        },
        destroyEditors() {
            Object.values(this.editors).forEach((editor) => {
                try {
                    editor?.destroy();
                } catch (e) {
                    // Ignore
                }
            });
            this.editors = {};
            this.sections.forEach((sec) => {
                const el = this.$refs['editor_' + sec.key];
                if (el) {
                    el.innerHTML = '';
                }
            });
        },
        switchTab(key) {
            if (this.activeTab === key) return;
            if (this.editors[this.activeTab]) {
                this.form[this.activeTab] = this.editors[this.activeTab].getHTML();
            }
            this.activeTab = key;
            this.$nextTick(() => {
                this.initTabEditor(key);
                this.focusActiveEditor();
            });
        },

        resetForm() {
            const today = getTodayLocalDate();
            this.form = {
                nomor_kka: '',
                pka_id: '',
                tanggal_dibuat: today,
                tanggal_direviu: today,
                tanggal_kka: today,
                tempat: '',
                auditee_level: 'Nasional',
                auditee_plotting_id: '',
                province_code: '',
                city_code: '',
                district_code: '',
                village_code: '',
                template_kka_id: '',
                tujuan: '',
                langkah: '',
                hasil: '',
                status: 'Draft',
            };
            this.resetRegionOptions();
            this.errors = {};
        },
        openCreateModal() {
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
            this.activeTab = 'tujuan';
            this.modalOpen = true;
            this.$nextTick(() => {
                this.initTabEditor('tujuan');
                this.focusActiveEditor();
            });
        },
        async openEditModal(row) {
            this.destroyEditors();
            this.editingId = row.id;
            this.resetForm();
            this.activeTab = 'tujuan';
            this.modalOpen = true;

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    throw new Error('Gagal memuat data KKA');
                }
                const data = await response.json();

                this.form = {
                    nomor_kka: data.nomor_kka || '',
                    pka_id: data.pka_id || '',
                    tanggal_dibuat: data.tanggal_dibuat || '',
                    tanggal_direviu: data.tanggal_direviu || '',
                    tanggal_kka: data.tanggal_kka || '',
                    tempat: data.tempat || '',
                    auditee_level: data.auditee_level || 'Nasional',
                    auditee_plotting_id: data.auditee_plotting_id || '',
                    province_code: data.province_code || '',
                    city_code: data.city_code || '',
                    district_code: data.district_code || '',
                    village_code: data.village_code || '',
                    template_kka_id: data.template_kka_id || '',
                    tujuan: this.decodeHtml(data.tujuan || ''),
                    langkah: this.decodeHtml(data.langkah || ''),
                    hasil: this.decodeHtml(data.hasil || ''),
                    status: data.status || 'Draft',
                };

                const [cities, districts, villages] = await Promise.all([
                    this.form.province_code ? this.fetchOptions('cities', this.form.province_code) : [],
                    this.form.city_code ? this.fetchOptions('districts', this.form.city_code) : [],
                    this.form.district_code ? this.fetchOptions('villages', this.form.district_code) : [],
                ]);
                this.cities = cities;
                this.districts = districts;
                this.villages = villages;

                this.$nextTick(() => {
                    this.initTabEditor('tujuan');
                    this.focusActiveEditor();
                });
            } catch (e) {
                console.error(e);
                this.errors = { general: [e.message || 'Gagal memuat data KKA.'] };
            }
        },
        closeModal(force = false) {
            if (this.saving && !force) return;
            this.modalOpen = false;
            this.destroyEditors();
            this.editingId = null;
            this.resetForm();
        },
        saveCurrentTab() {
            if (this.editors[this.activeTab]) {
                this.form[this.activeTab] = this.editors[this.activeTab].getHTML();
            }
            const currentSection = this.sections.find((s) => s.key === this.activeTab);
            this.showFlash(`Bagian ${currentSection ? currentSection.label : ''} tersimpan di formulir.`);
        },
        async saveKka() {
            this.saving = true;
            this.errors = {};

            Object.keys(this.editors).forEach((key) => {
                if (this.editors[key]) {
                    this.form[key] = this.editors[key].getHTML();
                }
            });

            const url = this.editingId ? `${this.baseUrl}/${this.editingId}` : this.storeUrl;
            const method = this.editingId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.form),
                });

                const json = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && json.errors) {
                        this.errors = json.errors;
                        const firstKey = Object.keys(json.errors)[0];
                        const firstMsg = json.errors[firstKey]?.[0] || 'Validasi formulir gagal.';
                        this.errors.general = [`Terdapat kesalahan pengisian: ${firstMsg}`];
                    } else {
                        this.errors = { general: [json.message || 'Terjadi kesalahan saat menyimpan data.'] };
                    }
                    this.$refs.modalScrollArea?.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                this.closeModal(true);
                this.showFlash(json.message || 'Data KKA berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                this.errors = { general: [e.message || 'Terjadi kesalahan jaringan.'] };
                this.$refs.modalScrollArea?.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                this.saving = false;
            }
        },

        // Duplikasi, hapus, dan status
        async confirmDuplicate(row) {
            const confirmed = await window.notify.confirm({
                title: 'Duplikasi KKA?',
                text: `KKA untuk PKA ${row.nomor_pka || ''} akan diduplikasi sebagai Draft baru.`,
                confirmText: 'Ya, duplikasi',
            });
            if (!confirmed) return;

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menduplikasi KKA.');
                }
                this.showFlash(json.message || 'KKA berhasil diduplikasi.');
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menduplikasi KKA.');
            }
        },
        async confirmDelete(row) {
            const confirmed = await window.notify.confirmDelete({ label: window.notify.rowLabel(row) });
            if (!confirmed) return;

            this.deleting = true;
            try {
                const response = await fetch(`${this.baseUrl}/${row.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await response.json();
                if (!response.ok) {
                    throw new Error(json.message || 'Gagal menghapus data KKA.');
                }
                this.showFlash(json.message || 'KKA berhasil dihapus.');
                if (this.rows.length === 1 && this.page > 1) {
                    this.page--;
                }
                this.fetchData();
            } catch (e) {
                console.error(e);
                window.notify.error(e.message || 'Terjadi kesalahan saat menghapus data KKA.');
            } finally {
                this.deleting = false;
            }
        },
        async toggleRowStatus(row, targetStatus = null) {
            if (row.updatingStatus) return;
            const currentStatus = row.status || 'Draft';
            const newStatus = targetStatus ? targetStatus : (currentStatus === 'Final' ? 'Draft' : 'Final');
            if (targetStatus && targetStatus === currentStatus) return;

            row.updatingStatus = true;
            row.status = newStatus;

            try {
                const response = await fetch(`${this.baseUrl}/${row.id}/status`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });
                const json = await response.json();
                if (!response.ok) {
                    throw new Error(json.message || 'Gagal mengubah status KKA.');
                }
                this.showFlash(json.message || `Status KKA berhasil diubah menjadi ${newStatus}.`);
            } catch (e) {
                console.error(e);
                row.status = currentStatus;
                this.showFlash(e.message || 'Terjadi kesalahan saat mengubah status.', 'error');
            } finally {
                row.updatingStatus = false;
            }
        },

        // Reviu Katim, Dalnis, dan Daltu
        reviewStatus(row, type) {
            return row[`latest_${type}_review_status`] || null;
        },
        reviewDate(row, type) {
            return row[`latest_${type}_review_date`] || null;
        },
        canReview(row, type) {
            return Boolean(row[`can_review_${type}`]);
        },
        reviewTypeLabel() {
            return this.reviewTypes.find((t) => t.key === this.reviewType)?.label || '';
        },
        openReviewModal(row, type = 'katim') {
            this.reviewType = type;
            this.reviewKka = {
                ...row,
                review_type: type,
                can_review: this.canReview(row, type),
                latest_review_status: this.reviewStatus(row, type),
                latest_review_date: this.reviewDate(row, type),
            };
            this.reviewLogs = [];
            this.reviewModalOpen = true;
            this.reviewFormOpen = false;
            this.reviewForm = { status: 'Disetujui', catatan: '' };
            this.reviewErrors = {};
            this.fetchReviews(row.id, type);
        },
        closeReviewModal() {
            if (this.savingReview) return;
            this.reviewModalOpen = false;
        },
        async fetchReviews(kkaId, type) {
            this.loadingReviews = true;
            try {
                const response = await fetch(`${this.baseUrl}/${kkaId}/reviews/${type}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await response.json();
                if (response.ok) {
                    if (json.kka) {
                        this.reviewKka = { ...this.reviewKka, ...json.kka };
                    }
                    this.reviewLogs = json.reviews || [];
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingReviews = false;
            }
        },
        async saveReview() {
            if (!this.reviewKka || !this.reviewKka.can_review) return;
            this.savingReview = true;
            this.reviewErrors = {};

            try {
                const response = await fetch(`${this.baseUrl}/${this.reviewKka.id}/reviews/${this.reviewType}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.reviewForm),
                });
                const json = await response.json();

                if (response.status === 422) {
                    this.reviewErrors = json.errors || {};
                    return;
                }
                if (!response.ok) {
                    this.reviewErrors = { status: [json.message || 'Gagal menyimpan reviu.'] };
                    return;
                }

                if (json.review) {
                    this.reviewLogs.unshift(json.review);
                }
                if (this.reviewKka) {
                    this.reviewKka.latest_review_status = json.latest_status;
                    this.reviewKka.latest_review_date = json.latest_review_date;
                }

                this.reviewFormOpen = false;
                this.reviewForm = { status: 'Disetujui', catatan: '' };
                this.showFlash(json.message || 'Reviu berhasil disimpan.');
                this.fetchData();
            } catch (e) {
                console.error(e);
            } finally {
                this.savingReview = false;
            }
        },
    }));
}
