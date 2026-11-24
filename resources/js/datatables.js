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
        showFlash(message) {
            this.flashMessage = message;
            setTimeout(() => {
                if (this.flashMessage === message) {
                    this.flashMessage = '';
                }
            }, 3500);
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
        sortCol: 1,
        sortDir: 'asc',
        flashMessage: '',
        columns: config.columns || [],
        indexUrl: config.indexUrl,
        storeUrl: config.storeUrl,
        baseUrl: config.baseUrl,
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
        confirmDelete(row) {
            this.deletingItem = row;
            this.deleteError = '';
            this.deleteModalOpen = true;
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
        confirmDelete(row) {
            this.deletingItem = row;
            this.deleteModalOpen = true;
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
        confirmDelete(row) {
            this.deletingItem = row;
            this.deleteError = '';
            this.deleteModalOpen = true;
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
            } finally {
                this.deleting = false;
            }
        },
    }));

    Alpine.data('timDataTable', (config) => ({
        ...getBaseDataTableState(config),
        ...getBaseDataTableMethods(),
        filterLingkup: '',
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
        form: {
            nama_tim: '',
            lingkup: 'Nasional',
            plotting_id: '',
            province_code: '',
            daltu_id: '',
            dalnis_id: '',
            ketua_tim_id: '',
            ketua_tim_2_id: '',
            auditor_ids: [],
        },
        errors: {},

        get lingkupOptions() {
            return this.lingkups.map((l) => ({ code: l, name: l }));
        },
        get plottingOptions() {
            return this.plottings.map((p) => ({ code: String(p.id), name: p.name }));
        },
        get availableKetuaTim1Options() {
            const kt2 = String(this.form.ketua_tim_2_id || '');
            return this.ketuaTimOptions.filter((u) => String(u.code) !== kt2);
        },
        get availableKetuaTim2Options() {
            const kt1 = String(this.form.ketua_tim_id || '');
            return this.ketuaTimOptions.filter((u) => String(u.code) !== kt1);
        },
        get availableAuditorCandidates() {
            const excludedIds = [
                Number(this.form.ketua_tim_id || 0),
                Number(this.form.ketua_tim_2_id || 0),
            ].filter((id) => id > 0);

            return this.auditorCandidates.filter((u) => !excludedIds.includes(Number(u.id)));
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
            this.form = {
                nama_tim: '',
                lingkup: 'Nasional',
                plotting_id: '',
                province_code: '',
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
            this.form = {
                nama_tim: row.nama_tim || '',
                lingkup: row.lingkup || 'Nasional',
                plotting_id: row.plotting_id ? String(row.plotting_id) : '',
                province_code: row.province_code || '',
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
            } else {
                this.form.plotting_id = '';
            }
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
        confirmDelete(row) {
            this.deletingItem = row;
            this.deleteError = '';
            this.deleteModalOpen = true;
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
            } finally {
                this.deleting = false;
            }
        },
    }));
}
