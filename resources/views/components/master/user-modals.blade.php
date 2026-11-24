{{-- Modal Tambah / Edit User --}}
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
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Data User' : 'Tambah Data User'"></h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Lengkapi informasi akun, peran, serta cakupan wilayah kerja user.
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

        <form x-on:submit.prevent="saveUser()" class="max-h-[80vh] space-y-5 overflow-y-auto px-6 py-5">
            {{-- Informasi Dasar --}}
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- 1. Nama Lengkap --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.name"
                        placeholder="Masukkan nama lengkap..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.name">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.name[0]"></p>
                    </template>
                </div>

                {{-- 2. Username --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.username"
                        placeholder="Masukkan username..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.username">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.username[0]"></p>
                    </template>
                </div>

                {{-- 3. Email --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        x-model="form.email"
                        placeholder="nama@email.com"
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.email">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.email[0]"></p>
                    </template>
                </div>

                {{-- 4. Nomor HP --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Nomor HP <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.phone"
                        placeholder="Contoh: 081234567890"
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.phone">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.phone[0]"></p>
                    </template>
                </div>

                {{-- 5. Peran --}}
                <div class="space-y-1.5 sm:col-span-2">
                    <x-searchable-select
                        label="Peran *"
                        placeholder="Pilih Peran"
                        itemsExpr="roleOptions"
                        valueModel="form.role"
                        changeAction="onRoleChange()"
                    />
                    <template x-if="errors.role">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.role[0]"></p>
                    </template>
                </div>

                {{-- 6. Password --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Password <span x-show="!editingId" class="text-red-500">*</span>
                        <span x-show="editingId" class="font-normal text-neutral-400">(Kosongkan jika tidak diubah)</span>
                    </label>
                    <input
                        type="password"
                        x-model="form.password"
                        placeholder="Minimal 8 karakter..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.password">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.password[0]"></p>
                    </template>
                </div>

                {{-- 7. Konfirmasi Password --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Konfirmasi Password <span x-show="!editingId" class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        x-model="form.password_confirmation"
                        placeholder="Ulangi password..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                </div>
            </div>

            {{-- 8. Bagian Khusus Auditor & Auditee --}}
            <template x-if="needsRegionalScope()">
                <div class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-400">
                            Cakupan Level & Wilayah (<span x-text="form.role"></span>)
                        </h3>
                    </div>

                    {{-- Pilihan Level (Nasional, Provinsi, Kabupaten, Kecamatan, Desa) --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                            Level <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                            <template x-for="lvl in levels" :key="lvl">
                                <button
                                    type="button"
                                    x-on:click="form.level = lvl; onLevelChange()"
                                    class="h-9 rounded-md border px-3 text-xs font-medium transition-colors"
                                    x-bind:class="form.level === lvl
                                        ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-900'
                                        : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                                    x-text="lvl"
                                ></button>
                            </template>
                        </div>
                        <template x-if="errors.level">
                            <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.level[0]"></p>
                        </template>
                    </div>

                    {{-- Pilihan Wilayah Berjenjang Sesuai Level --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        {{-- Plot Wilayah (WIL dari tabel plotting) - Hanya muncul jika Level = Nasional --}}
                        <template x-if="showPlottingField()">
                            <div class="space-y-1.5 sm:col-span-2">
                                <x-searchable-select
                                    label="Plot Wilayah (WIL) *"
                                    placeholder="Pilih WIL (Plotting)"
                                    itemsExpr="plottingOptions"
                                    valueModel="form.plotting_id"
                                    changeAction=""
                                />
                                <template x-if="errors.plotting_id">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.plotting_id[0]"></p>
                                </template>
                            </div>
                        </template>

                        {{-- Provinsi (Muncul untuk Level Provinsi, Kabupaten, Kecamatan, Desa) --}}
                        <template x-if="showProvinceField()">
                            <div class="space-y-1.5">
                                <x-searchable-select
                                    label="Provinsi *"
                                    placeholder="Pilih Provinsi"
                                    itemsExpr="modalProvinces"
                                    valueModel="form.province_code"
                                    changeAction="onModalProvinceChange()"
                                />
                                <template x-if="errors.province_code">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.province_code[0]"></p>
                                </template>
                            </div>
                        </template>

                        {{-- Kabupaten / Kota (Muncul untuk Level Kabupaten, Kecamatan, Desa) --}}
                        <template x-if="showCityField()">
                            <div class="space-y-1.5">
                                <x-searchable-select
                                    label="Kabupaten / Kota *"
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

                        {{-- Kecamatan (Muncul untuk Level Kecamatan, Desa) --}}
                        <template x-if="showDistrictField()">
                            <div class="space-y-1.5">
                                <x-searchable-select
                                    label="Kecamatan *"
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

                        {{-- Desa / Kelurahan (Muncul hanya untuk Level Desa) --}}
                        <template x-if="showVillageField()">
                            <div class="space-y-1.5">
                                <x-searchable-select
                                    label="Desa / Kelurahan *"
                                    placeholder="Pilih Desa/Kelurahan"
                                    itemsExpr="modalVillages"
                                    valueModel="form.village_code"
                                    changeAction="onModalVillageChange()"
                                    disabledExpr="!form.district_code"
                                    disabledMessage="Silahkan pilih kecamatan terlebih dahulu"
                                />
                                <template x-if="errors.village_code">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.village_code[0]"></p>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Tambahan Khusus Auditee: Bidang --}}
                    <template x-if="isAuditee()">
                        <div class="space-y-4 border-t border-neutral-200 pt-4 dark:border-neutral-800">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5 sm:col-span-2">
                                    <x-searchable-select
                                        label="Bidang *"
                                        placeholder="Pilih Bidang"
                                        itemsExpr="bidangOptions"
                                        valueModel="form.bidang"
                                        changeAction=""
                                    />
                                    <template x-if="errors.bidang">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.bidang[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Jabatan & Upload TTD (Opsional untuk Semua Peran & Level) --}}
            <div class="grid gap-4 rounded-xl border border-neutral-200 bg-neutral-50/40 p-4 sm:grid-cols-2 dark:border-neutral-800 dark:bg-neutral-900/20">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Jabatan <span class="font-normal text-neutral-400">(Opsional)</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.jabatan"
                        placeholder="Masukkan jabatan..."
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100"
                    />
                    <template x-if="errors.jabatan">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.jabatan[0]"></p>
                    </template>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                        Upload Tanda Tangan (TTD) <span class="font-normal text-neutral-400">(Opsional)</span>
                    </label>
                    <input
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        x-on:change="onTtdChange($event)"
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs text-neutral-700 file:mr-3 file:rounded file:border-0 file:bg-neutral-900 file:px-2.5 file:py-1 file:text-xs file:font-medium file:text-white hover:file:bg-neutral-800 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:file:bg-white dark:file:text-neutral-900"
                    />
                    <p class="text-[11px] text-neutral-500">Format: PNG, JPG, atau WEBP (Maks. 2MB).</p>

                    <template x-if="ttdPreviewUrl || existingTtdUrl">
                        <div class="inline-flex items-center gap-3 rounded-lg border border-neutral-200 bg-white p-2 dark:border-neutral-800 dark:bg-neutral-950">
                            <img
                                :src="ttdPreviewUrl || existingTtdUrl"
                                alt="Preview TTD"
                                class="h-12 w-auto rounded object-contain"
                            />
                            <span class="text-xs text-neutral-500" x-text="ttdPreviewUrl ? 'File TTD Baru' : 'TTD Saat Ini'"></span>
                        </div>
                    </template>

                    <template x-if="errors.ttd">
                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.ttd[0]"></p>
                    </template>
                </div>
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
                    <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan User'"></span>
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
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Data User</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin menghapus user <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="deletingItem?.name"></span>?
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
                x-on:click="deleteUser()"
                x-bind:disabled="deleting"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-4 text-sm font-medium text-white shadow-xs hover:bg-red-700 disabled:opacity-50"
            >
                <x-lucide-loader-circle x-show="deleting" x-cloak class="h-4 w-4 animate-spin" />
                <span>Hapus</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Reset Password --}}
<div
    x-show="resetPasswordModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div
        x-show="resetPasswordModalOpen"
        x-transition.opacity
        x-on:click="resetPasswordModalOpen = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
    ></div>

    <div
        x-show="resetPasswordModalOpen"
        x-transition
        class="relative z-10 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Reset Password User</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin mereset password <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="resettingItem?.name"></span> menjadi default <code class="rounded bg-neutral-100 px-1.5 py-0.5 font-mono text-xs font-semibold text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100">Password123</code>?
        </p>

        <div class="mt-6 flex items-center justify-end gap-2">
            <button
                type="button"
                x-on:click="resetPasswordModalOpen = false"
                class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                Batal
            </button>
            <button
                type="button"
                x-on:click="executeResetPassword()"
                x-bind:disabled="resettingPassword"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-amber-500 px-4 text-sm font-medium text-white shadow-xs hover:bg-amber-600 disabled:opacity-50"
            >
                <x-lucide-loader-circle x-show="resettingPassword" x-cloak class="h-4 w-4 animate-spin" />
                <span>Reset Password</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Impersonate --}}
<div
    x-show="impersonateModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div
        x-show="impersonateModalOpen"
        x-transition.opacity
        x-on:click="impersonateModalOpen = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"
    ></div>

    <div
        x-show="impersonateModalOpen"
        x-transition
        class="relative z-10 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Impersonate User</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin masuk sebagai <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="impersonatingItem?.name"></span>?
        </p>

        <div class="mt-6 flex items-center justify-end gap-2">
            <button
                type="button"
                x-on:click="impersonateModalOpen = false"
                class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                Batal
            </button>
            <button
                type="button"
                x-on:click="executeImpersonate()"
                x-bind:disabled="impersonating"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-indigo-600 px-4 text-sm font-medium text-white shadow-xs hover:bg-indigo-700 disabled:opacity-50"
            >
                <x-lucide-loader-circle x-show="impersonating" x-cloak class="h-4 w-4 animate-spin" />
                <span>Masuk Sekarang</span>
            </button>
        </div>
    </div>
</div>
