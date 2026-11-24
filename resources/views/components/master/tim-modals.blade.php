{{-- Modal Tambah / Edit Tim --}}
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
        class="relative z-10 my-8 w-full max-w-3xl overflow-visible rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
            <div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Data Tim' : 'Tambah Data Tim'"></h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Lengkapi nama tim, cakupan lingkup, pengendali mutu, pengawas, ketua tim, dan anggota auditor.
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

        <form x-on:submit.prevent="saveTim()" class="max-h-[80vh] space-y-5 overflow-y-auto px-6 py-5">
            {{-- 1. Nama Tim --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                    Nama Tim <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    x-model="form.nama_tim"
                    placeholder="Masukkan nama tim..."
                    class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                />
                <template x-if="errors.nama_tim">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nama_tim[0]"></p>
                </template>
            </div>

            {{-- 2. Lingkup & Pilihan Wilayah / WIL --}}
            <div class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            Lingkup <span class="text-red-500">*</span>
                        </label>
                        <template x-if="isLingkupReadonly()">
                            <span class="text-[11px] italic text-neutral-400 dark:text-neutral-500">
                                Readonly (Otomatis <span x-text="form.lingkup"></span>)
                            </span>
                        </template>
                    </div>
                    <div class="grid gap-2" :class="lingkups.length === 1 ? 'grid-cols-1' : 'grid-cols-2'">
                        <template x-for="lk in lingkups" :key="lk">
                            <button
                                type="button"
                                x-bind:disabled="isLingkupReadonly()"
                                x-on:click="if (!isLingkupReadonly()) { form.lingkup = lk; onLingkupChange(); }"
                                class="h-9 rounded-md border px-3 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-80"
                                x-bind:class="form.lingkup === lk
                                    ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-900'
                                    : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                x-text="lk"
                            ></button>
                        </template>
                    </div>
                    <template x-if="errors.lingkup">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.lingkup[0]"></p>
                    </template>
                </div>

                {{-- Jika Nasional muncul opsi WIL 1 - 4 --}}
                <template x-if="form.lingkup === 'Nasional'">
                    <div class="space-y-1.5">
                        <x-searchable-select
                            label="Plot Wilayah (WIL) *"
                            placeholder="Pilih WIL 1 - 4"
                            itemsExpr="plottingOptions"
                            valueModel="form.plotting_id"
                            changeAction="onScopeValueChange()"
                            disabledExpr="isPlottingReadonly()"
                            disabledMessage="Plot wilayah otomatis mengikuti plot akun Admin Nasional Anda"
                        />
                        <template x-if="errors.plotting_id">
                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.plotting_id[0]"></p>
                        </template>
                    </div>
                </template>

                {{-- Jika Wilayah muncul semua provinsi --}}
                <template x-if="form.lingkup === 'Wilayah'">
                    <div class="space-y-1.5">
                        <x-searchable-select
                            label="Provinsi *"
                            placeholder="Pilih Provinsi"
                            itemsExpr="allProvinces"
                            valueModel="form.province_code"
                            changeAction="onScopeValueChange()"
                            disabledExpr="isProvinceReadonly()"
                            disabledMessage="Provinsi otomatis mengikuti wilayah akun Admin Wilayah Anda"
                        />
                        <template x-if="errors.province_code">
                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.province_code[0]"></p>
                        </template>
                    </div>
                </template>
            </div>

            {{-- 3 - 6. Susunan Pejabat Tim --}}
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- 3. Pengendali Mutu (Daltu) --}}
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Pengendali Mutu (Daltu) *"
                        placeholder="Pilih Pengendali Mutu"
                        itemsExpr="filteredDaltuOptions"
                        valueModel="form.daltu_id"
                        changeAction=""
                    />
                    <template x-if="errors.daltu_id">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.daltu_id[0]"></p>
                    </template>
                </div>

                {{-- 4. Pengawas Tim (Dalnis) --}}
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Pengawas Tim (Dalnis) *"
                        placeholder="Pilih Pengawas Tim"
                        itemsExpr="filteredDalnisOptions"
                        valueModel="form.dalnis_id"
                        changeAction=""
                    />
                    <template x-if="errors.dalnis_id">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.dalnis_id[0]"></p>
                    </template>
                </div>

                {{-- 5. Ketua Tim --}}
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Ketua Tim *"
                        placeholder="Pilih Ketua Tim"
                        itemsExpr="availableKetuaTim1Options"
                        valueModel="form.ketua_tim_id"
                        changeAction="onKetuaTimChange()"
                    />
                    <template x-if="errors.ketua_tim_id">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.ketua_tim_id[0]"></p>
                    </template>
                </div>

                {{-- 6. Ketua Tim 2 (Opsional) --}}
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Ketua Tim 2 (Opsional)"
                        placeholder="Pilih Ketua Tim 2 (Opsional)"
                        itemsExpr="availableKetuaTim2Options"
                        valueModel="form.ketua_tim_2_id"
                        changeAction="onKetuaTimChange()"
                    />
                    <template x-if="errors.ketua_tim_2_id">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.ketua_tim_2_id[0]"></p>
                    </template>
                </div>
            </div>

            {{-- 7. Anggota (Auditor) - Multiple Select Dropdown --}}
            <div
                class="relative space-y-1.5"
                x-data="{
                    openAuditorDropdown: false,
                    auditorHighlightIndex: 0,
                    toggleAuditorDropdown() {
                        this.openAuditorDropdown = !this.openAuditorDropdown;
                        if (this.openAuditorDropdown) {
                            this.auditorQuery = '';
                            this.auditorHighlightIndex = this.filteredModalAuditors.length > 0 ? 0 : -1;
                            this.$nextTick(() => {
                                this.$refs.auditorSearchInput?.focus();
                                this.scrollAuditorHighlight();
                            });
                        }
                    },
                    moveAuditorDown() {
                        const max = this.filteredModalAuditors.length - 1;
                        if (max < 0) {
                            this.auditorHighlightIndex = -1;
                            return;
                        }
                        this.auditorHighlightIndex = this.auditorHighlightIndex < max ? this.auditorHighlightIndex + 1 : 0;
                        this.scrollAuditorHighlight();
                    },
                    moveAuditorUp() {
                        const max = this.filteredModalAuditors.length - 1;
                        if (max < 0) {
                            this.auditorHighlightIndex = -1;
                            return;
                        }
                        this.auditorHighlightIndex = this.auditorHighlightIndex > 0 ? this.auditorHighlightIndex - 1 : max;
                        this.scrollAuditorHighlight();
                    },
                    selectHighlightedAuditor() {
                        const item = this.filteredModalAuditors[this.auditorHighlightIndex];
                        if (item) {
                            this.toggleAuditor(item.id);
                        }
                    },
                    scrollAuditorHighlight() {
                        this.$nextTick(() => {
                            const list = this.$refs.auditorOptionsList;
                            if (!list) return;
                            const el = list.querySelector(`[data-auditor-index='${this.auditorHighlightIndex}']`);
                            if (el) {
                                el.scrollIntoView({ block: 'nearest' });
                            }
                        });
                    }
                }"
                x-on:click.outside="openAuditorDropdown = false"
                x-on:keydown.escape.window="openAuditorDropdown = false"
            >
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">
                        Anggota (Auditor) <span class="text-red-500">*</span>
                    </label>
                    <button
                        type="button"
                        x-show="form.auditor_ids.length > 0"
                        x-cloak
                        x-on:click="clearSelectedAuditors()"
                        class="text-xs font-medium text-neutral-500 hover:text-neutral-800 hover:underline dark:text-neutral-400 dark:hover:text-neutral-200"
                    >
                        Reset Pilihan (<span x-text="form.auditor_ids.length"></span>)
                    </button>
                </div>

                {{-- Trigger Dropdown --}}
                <button
                    type="button"
                    x-on:click="toggleAuditorDropdown()"
                    x-on:keydown.arrow-down.prevent="if (!openAuditorDropdown) toggleAuditorDropdown(); else moveAuditorDown()"
                    x-on:keydown.arrow-up.prevent="if (!openAuditorDropdown) toggleAuditorDropdown(); else moveAuditorUp()"
                    class="flex min-h-9 w-full items-center justify-between gap-2 rounded-md border border-neutral-200 bg-white px-3 py-1.5 text-left text-sm text-neutral-900 shadow-xs transition-colors hover:bg-neutral-50 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100 dark:hover:bg-neutral-900 dark:focus:border-neutral-100 dark:focus:ring-white/20"
                >
                    <div class="flex flex-1 flex-wrap items-center gap-1.5">
                        <template x-if="form.auditor_ids.length === 0">
                            <span class="text-neutral-500 dark:text-neutral-400">Pilih Anggota (Auditor)...</span>
                        </template>

                        <template x-for="id in form.auditor_ids" :key="id">
                            <span class="inline-flex items-center gap-1 rounded-md border border-neutral-200 bg-neutral-100/80 px-2 py-0.5 text-xs font-medium text-neutral-800 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                                <span x-text="getAuditorById(id)?.name || id"></span>
                                <span
                                    role="button"
                                    tabindex="0"
                                    x-on:click.stop="toggleAuditor(id)"
                                    class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200"
                                >
                                    <x-lucide-x class="h-3 w-3" />
                                </span>
                            </span>
                        </template>
                    </div>
                    <x-lucide-chevrons-up-down class="h-3.5 w-3.5 shrink-0 text-neutral-400" />
                </button>

                {{-- Dropdown Menu --}}
                <div
                    x-show="openAuditorDropdown"
                    x-cloak
                    x-transition.opacity.duration.100ms
                    class="absolute left-0 right-0 z-30 mt-1 overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-950"
                >
                    <div class="relative border-b border-neutral-200 p-1.5 dark:border-neutral-800">
                        <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-neutral-400" />
                        <input
                            x-ref="auditorSearchInput"
                            type="search"
                            x-model="auditorQuery"
                            x-on:input="auditorHighlightIndex = filteredModalAuditors.length > 0 ? 0 : -1; scrollAuditorHighlight()"
                            x-on:keydown.arrow-down.prevent="moveAuditorDown()"
                            x-on:keydown.arrow-up.prevent="moveAuditorUp()"
                            x-on:keydown.enter.prevent="selectHighlightedAuditor()"
                            placeholder="Cari anggota (auditor)..."
                            class="block h-8 w-full rounded-md border border-neutral-200 bg-neutral-50/60 pl-8 pr-2.5 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-neutral-100 dark:focus:bg-neutral-950"
                        />
                    </div>

                    <ul x-ref="auditorOptionsList" class="max-h-56 space-y-0.5 overflow-y-auto p-1 text-sm">
                        <template x-for="(user, idx) in filteredModalAuditors" :key="user.id">
                            <li>
                                <button
                                    type="button"
                                    x-bind:data-auditor-index="idx"
                                    x-on:click="toggleAuditor(user.id)"
                                    x-on:mouseenter="auditorHighlightIndex = idx"
                                    class="flex w-full items-center justify-between gap-2 rounded-md px-2.5 py-1.5 text-left text-sm transition-colors"
                                    x-bind:class="[
                                        isAuditorSelected(user.id)
                                            ? 'bg-emerald-50 font-medium text-emerald-800 hover:bg-emerald-100/80 dark:bg-emerald-950/50 dark:text-emerald-300 dark:hover:bg-emerald-900/50'
                                            : 'text-neutral-700 hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-neutral-800',
                                        auditorHighlightIndex === idx && !isAuditorSelected(user.id)
                                            ? 'bg-neutral-100 dark:bg-neutral-800'
                                            : '',
                                        auditorHighlightIndex === idx && isAuditorSelected(user.id)
                                            ? 'ring-1 ring-emerald-300 dark:ring-emerald-700'
                                            : ''
                                    ]"
                                >
                                    <span class="truncate" x-text="user.name"></span>
                                    <x-lucide-check x-show="isAuditorSelected(user.id)" x-cloak class="h-3.5 w-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                </button>
                            </li>
                        </template>

                        <template x-if="filteredModalAuditors.length === 0">
                            <li class="px-2.5 py-3 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                Tidak ditemukan.
                            </li>
                        </template>
                    </ul>
                </div>

                <template x-if="errors.auditor_ids">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.auditor_ids[0]"></p>
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
                    <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan Tim'"></span>
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
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Data Tim</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin menghapus tim <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="deletingItem?.nama_tim"></span>? Tindakan ini tidak dapat dibatalkan.
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
                x-on:click="deleteTim()"
                x-bind:disabled="deleting"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-4 text-sm font-medium text-white shadow-xs hover:bg-red-700 disabled:opacity-50"
            >
                <x-lucide-loader-circle x-show="deleting" x-cloak class="h-4 w-4 animate-spin" />
                <span>Hapus</span>
            </button>
        </div>
    </div>
</div>
