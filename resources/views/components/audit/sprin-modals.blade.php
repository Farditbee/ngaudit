{{-- Modal Tambah / Edit Sprin --}}
<div
    x-show="modalOpen"
    x-cloak
    x-on:keydown.escape.window="closeModal()"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
>
    <div x-show="modalOpen" x-transition.opacity x-on:click="closeModal()" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

    <div
        x-show="modalOpen"
        x-transition
        class="relative z-10 my-8 w-full max-w-3xl overflow-visible rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
            <div>
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100" x-text="editingId ? 'Edit Data Sprin' : 'Tambah Data Sprin'"></h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">Lengkapi nomor, dokumen, tim, tahap, dan periode Sprin.</p>
            </div>
            <button type="button" x-on:click="closeModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        <form x-on:submit.prevent="saveSprin()" class="max-h-[80vh] space-y-5 overflow-y-auto px-6 py-5">
            <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Nomor Sprin <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.no_sprin" placeholder="Masukkan nomor Sprin..." class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100" />
                <template x-if="errors.no_sprin">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.no_sprin[0]"></p>
                </template>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Dokumen Sprin <span class="text-red-500" x-show="!editingId">*</span></label>
                <input type="file" x-ref="filePerintah" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" x-on:change="files.file_surat_perintah = $event.target.files[0] || null" class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs text-neutral-700 file:mr-3 file:rounded file:border-0 file:bg-neutral-900 file:px-2.5 file:py-1 file:text-xs file:font-medium file:text-white hover:file:bg-neutral-800 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:file:bg-white dark:file:text-neutral-900" />
                <p class="text-[11px] text-neutral-500">Format: PDF, DOC, DOCX, JPG, atau PNG (Maks. 10MB).</p>
                <template x-if="editingId && !files.file_surat_perintah && existing.perintah">
                    <p class="text-[11px] text-neutral-500">Dokumen saat ini: <span class="font-medium" x-text="existing.perintah"></span> (biarkan kosong jika tidak diganti)</p>
                </template>
                <template x-if="errors.file_surat_perintah">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.file_surat_perintah[0]"></p>
                </template>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Nomor Surat Pemberitahuan <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.no_surat_pemberitahuan" placeholder="Masukkan nomor surat pemberitahuan..." class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100" />
                <template x-if="errors.no_surat_pemberitahuan">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.no_surat_pemberitahuan[0]"></p>
                </template>
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Dokumen Surat Pemberitahuan <span class="text-red-500" x-show="!editingId">*</span></label>
                <input type="file" x-ref="filePemberitahuan" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" x-on:change="files.file_surat_pemberitahuan = $event.target.files[0] || null" class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs text-neutral-700 file:mr-3 file:rounded file:border-0 file:bg-neutral-900 file:px-2.5 file:py-1 file:text-xs file:font-medium file:text-white hover:file:bg-neutral-800 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:file:bg-white dark:file:text-neutral-900" />
                <p class="text-[11px] text-neutral-500">Format: PDF, DOC, DOCX, JPG, atau PNG (Maks. 10MB).</p>
                <template x-if="editingId && !files.file_surat_pemberitahuan && existing.pemberitahuan">
                    <p class="text-[11px] text-neutral-500">Dokumen saat ini: <span class="font-medium" x-text="existing.pemberitahuan"></span> (biarkan kosong jika tidak diganti)</p>
                </template>
                <template x-if="errors.file_surat_pemberitahuan">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.file_surat_pemberitahuan[0]"></p>
                </template>
            </div>
            </div>

            {{-- WIL / Provinsi mengikuti user yang login --}}
            <template x-if="scopeLabel">
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300" x-text="scopeLabel"></label>
                    <input type="text" readonly :value="scopeName || '-'" class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100 cursor-not-allowed bg-neutral-100 dark:bg-neutral-800" />
                </div>
            </template>

            {{-- Nama Tim --}}
            <div class="space-y-1.5">
                <x-searchable-select
                    label="Nama Tim *"
                    placeholder="Pilih Tim"
                    itemsExpr="timOptions"
                    valueModel="form.tim_id"
                    changeAction="onTimChange()"
                />
                <template x-if="errors.tim_id">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tim_id[0]"></p>
                </template>
            </div>

            {{-- Otomatis terisi dari Tim --}}
            <div class="grid gap-4 rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30 sm:grid-cols-2">
                <div>
                    <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Pengendali Mutu (Daltu)</p>
                    <p class="mt-0.5 text-sm font-medium text-neutral-900 dark:text-neutral-100" x-text="selectedTim?.daltu_name || '-'"></p>
                </div>
                <div>
                    <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Pengawas Tim (Dalnis)</p>
                    <p class="mt-0.5 text-sm font-medium text-neutral-900 dark:text-neutral-100" x-text="selectedTim?.dalnis_name || '-'"></p>
                </div>
                <div>
                    <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Ketua Tim</p>
                    <p class="mt-0.5 text-sm font-medium text-neutral-900 dark:text-neutral-100" x-text="selectedTim ? [selectedTim.ketua_tim_name, selectedTim.ketua_tim_2_name].filter(Boolean).join(', ') || '-' : '-'"></p>
                </div>
                <div>
                    <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Lingkup</p>
                    <p class="mt-0.5 text-sm font-medium text-neutral-900 dark:text-neutral-100" x-text="selectedTim ? selectedTim.lingkup + (selectedTim.scope_name ? ' - ' + selectedTim.scope_name : '') : '-'"></p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Anggota</p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        <template x-for="name in (selectedTim?.auditors || [])" :key="name">
                            <span class="inline-flex items-center rounded-md border border-neutral-200 bg-white px-2 py-0.5 text-xs text-neutral-800 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200" x-text="name"></span>
                        </template>
                        <template x-if="!selectedTim || selectedTim.auditors.length === 0">
                            <span class="text-sm text-neutral-500">-</span>
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-1.5">
                    <x-searchable-select
                        label="Tahap *"
                        placeholder="Pilih Tahap"
                        itemsExpr="tahapOptions"
                        valueModel="form.tahap"
                        changeAction=""
                    />
                    <template x-if="errors.tahap">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tahap[0]"></p>
                </template>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Tanggal Awal <span class="text-red-500">*</span></label>
                    <input
                        type="date"
                        x-model="form.tgl_mulai"
                        x-on:input="onTglMulaiChange($event.target.value)"
                        x-on:change="onTglMulaiChange($event.target.value)"
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.tgl_mulai">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tgl_mulai[0]"></p>
                </template>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Tanggal Akhir <span class="text-red-500">*</span></label>
                    <input
                        type="date"
                        x-model="form.tgl_akhir"
                        :min="form.tgl_mulai"
                        class="block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                    />
                    <template x-if="errors.tgl_akhir">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tgl_akhir[0]"></p>
                </template>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">Keterangan</label>
                <textarea x-model="form.keterangan" rows="3" placeholder="Masukkan keterangan..." class="block w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"></textarea>
                <template x-if="errors.keterangan">
                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.keterangan[0]"></p>
                </template>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-neutral-200 pt-4 dark:border-neutral-800">
                <button type="button" x-on:click="closeModal()" class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800">
                    Batal
                </button>
                <button type="submit" x-bind:disabled="saving" class="inline-flex h-9 items-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200">
                    <x-lucide-loader-circle x-show="saving" x-cloak class="h-4 w-4 animate-spin" />
                    <span x-text="editingId ? 'Simpan Perubahan' : 'Simpan Sprin'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div x-show="deleteModalOpen" x-transition.opacity x-on:click="deleteModalOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

    <div x-show="deleteModalOpen" x-transition class="relative z-10 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-6 shadow-xl dark:border-neutral-800 dark:bg-neutral-950">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Hapus Data Sprin</h3>
        <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
            Apakah Anda yakin ingin menghapus Sprin <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="deletingItem?.no_sprin"></span>?
        </p>

        <template x-if="deleteError">
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300" x-text="deleteError"></div>
        </template>

        <div class="mt-6 flex items-center justify-end gap-2">
            <button type="button" x-on:click="deleteModalOpen = false" class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-sm font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800">
                Batal
            </button>
            <button type="button" x-on:click="deleteSprin()" x-bind:disabled="deleting" class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-4 text-sm font-medium text-white shadow-xs hover:bg-red-700 disabled:opacity-50">
                <x-lucide-loader-circle x-show="deleting" x-cloak class="h-4 w-4 animate-spin" />
                <span>Hapus</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Reviu Sprin --}}
<div
    x-show="reviewModalOpen"
    x-cloak
    x-on:keydown.escape.window="closeReviewModal()"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
>
    <div x-show="reviewModalOpen" x-transition.opacity x-on:click="closeReviewModal()" class="fixed inset-0 bg-black/50 backdrop-blur-[1px]"></div>

    <div
        x-show="reviewModalOpen"
        x-transition
        class="relative z-10 my-8 w-full max-w-4xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-950"
    >
        {{-- Header Modal Reviu --}}
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                    <x-lucide-clipboard-check class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">
                        Lembar Reviu Sprin
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Pemeriksaan dan persetujuan Pengendali Mutu (Daltu).
                    </p>
                </div>
            </div>
            <button type="button" x-on:click="closeReviewModal()" class="rounded-md p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        <div class="max-h-[80vh] space-y-6 overflow-y-auto px-6 py-5">
            {{-- Ringkasan Data Sprin --}}
            <div class="rounded-xl border border-neutral-200 bg-neutral-50/60 p-4 dark:border-neutral-800 dark:bg-neutral-900/40">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 pb-3 dark:border-neutral-800">
                    <div class="space-y-0.5">
                        <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Nomor Sprin</span>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100" x-text="reviewSprin?.no_sprin || '-'"></h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-neutral-500">Status Saat Ini:</span>
                        <template x-if="reviewSprin?.latest_review_status === 'Disetujui'">
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                Disetujui
                            </span>
                        </template>
                        <template x-if="reviewSprin?.latest_review_status === 'Koreksi'">
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                Koreksi
                            </span>
                        </template>
                        <template x-if="!reviewSprin?.latest_review_status">
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                Menunggu Reviu
                            </span>
                        </template>
                    </div>
                </div>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Tipe & Lingkup</span>
                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="reviewSprin?.sprin_tipe === 'Nasional'
                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                x-text="reviewSprin?.sprin_tipe || '-'"
                            ></span>
                            <template x-if="reviewSprin?.sprin_tipe === 'Nasional' && reviewSprin?.plot_name">
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[11px] font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300" x-text="reviewSprin.plot_name"></span>
                            </template>
                            <template x-if="reviewSprin?.sprin_tipe === 'Wilayah' && reviewSprin?.province_name">
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300" x-text="reviewSprin.province_name"></span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Periode (Dari - Sampai)</span>
                        <p class="mt-1 text-xs font-medium text-neutral-800 dark:text-neutral-200">
                            <span x-text="reviewSprin?.tgl_mulai ? formatDate(reviewSprin.tgl_mulai) : '-'"></span>
                            <span class="text-neutral-400 mx-1">s/d</span>
                            <span x-text="reviewSprin?.tgl_akhir ? formatDate(reviewSprin.tgl_akhir) : '-'"></span>
                        </p>
                    </div>

                    <div>
                        <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Keterangan</span>
                        <p class="mt-1 text-xs text-neutral-800 dark:text-neutral-200 break-words" x-text="reviewSprin?.keterangan || '-'"></p>
                    </div>

                    <div>
                        <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Lampiran Dokumen Sprin</span>
                        <div class="mt-1">
                            <template x-if="reviewSprin?.surat_perintah_url">
                                <a :href="reviewSprin.surat_perintah_url" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-blue-400">
                                    <x-lucide-file-text class="h-3.5 w-3.5" />
                                    <span class="max-w-[14rem] truncate" x-text="reviewSprin.file_surat_perintah_name || 'Lihat Dokumen Sprin'"></span>
                                    <x-lucide-external-link class="h-3 w-3 opacity-60" />
                                </a>
                            </template>
                            <template x-if="!reviewSprin?.surat_perintah_url">
                                <span class="text-xs text-neutral-400">Tidak ada lampiran</span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] font-medium uppercase text-neutral-500 dark:text-neutral-400">Lampiran Surat Pemberitahuan</span>
                        <div class="mt-1">
                            <template x-if="reviewSprin?.surat_pemberitahuan_url">
                                <a :href="reviewSprin.surat_pemberitahuan_url" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-blue-400">
                                    <x-lucide-file-text class="h-3.5 w-3.5" />
                                    <span class="max-w-[14rem] truncate" x-text="reviewSprin.file_surat_pemberitahuan_name || 'Lihat Dokumen Pemberitahuan'"></span>
                                    <x-lucide-external-link class="h-3 w-3 opacity-60" />
                                </a>
                            </template>
                            <template x-if="!reviewSprin?.surat_pemberitahuan_url">
                                <span class="text-xs text-neutral-400">Tidak ada lampiran</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Log Reviu --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Log Riwayat Reviu</h4>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Catatan dan status hasil telaah oleh Pengendali Mutu.</p>
                    </div>
                    <template x-if="reviewSprin?.can_review">
                        <button
                            type="button"
                            x-on:click="reviewFormOpen = !reviewFormOpen"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md bg-neutral-900 px-3 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                        >
                            <x-lucide-plus x-show="!reviewFormOpen" class="h-3.5 w-3.5" />
                            <x-lucide-x x-show="reviewFormOpen" x-cloak class="h-3.5 w-3.5" />
                            <span x-text="reviewFormOpen ? 'Tutup Form' : 'Tambah Reviu'"></span>
                        </button>
                    </template>
                </div>

                {{-- Form Input Reviu Baru (Khusus Daltu dan Super Admin) --}}
                <template x-if="reviewSprin?.can_review">
                    <div
                        x-show="reviewFormOpen"
                        x-cloak
                        x-transition
                        class="rounded-xl border border-neutral-300 bg-neutral-50/80 p-4 dark:border-neutral-700 dark:bg-neutral-900/60"
                    >
                        <form x-on:submit.prevent="saveReview()" class="space-y-3">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Status Reviu <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2 sm:w-72">
                                    <label
                                        class="flex cursor-pointer items-center justify-center gap-1.5 rounded-md border p-2 text-xs font-medium transition-colors"
                                        :class="reviewForm.status === 'Disetujui'
                                            ? 'border-emerald-600 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600 dark:border-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-300'
                                            : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300'"
                                    >
                                        <input type="radio" x-model="reviewForm.status" value="Disetujui" class="sr-only" />
                                        <x-lucide-check-circle class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                                        <span>Disetujui</span>
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center justify-center gap-1.5 rounded-md border p-2 text-xs font-medium transition-colors"
                                        :class="reviewForm.status === 'Koreksi'
                                            ? 'border-amber-500 bg-amber-50 text-amber-800 ring-1 ring-amber-500 dark:border-amber-500 dark:bg-amber-950/50 dark:text-amber-300'
                                            : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300'"
                                    >
                                        <input type="radio" x-model="reviewForm.status" value="Koreksi" class="sr-only" />
                                        <x-lucide-alert-circle class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                        <span>Koreksi</span>
                                    </label>
                                </div>
                                <template x-if="reviewErrors.status">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="reviewErrors.status[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Catatan Reviu
                                </label>
                                <textarea
                                    x-model="reviewForm.catatan"
                                    rows="3"
                                    placeholder="Tuliskan catatan, rekomendasi, atau instruksi perbaikan..."
                                    class="block w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100"
                                ></textarea>
                                <template x-if="reviewErrors.catatan">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="reviewErrors.catatan[0]"></p>
                                </template>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button
                                    type="button"
                                    x-on:click="reviewFormOpen = false"
                                    class="inline-flex h-8 items-center rounded-md border border-neutral-200 bg-white px-3 text-xs font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    x-bind:disabled="savingReview"
                                    class="inline-flex h-8 items-center gap-1.5 rounded-md bg-neutral-900 px-3 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                                >
                                    <x-lucide-loader-circle x-show="savingReview" x-cloak class="h-3.5 w-3.5 animate-spin" />
                                    <span>Simpan Reviu</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

                {{-- Tabel DataTable Log Reviu --}}
                <div class="relative overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-800">
                    <div
                        x-show="loadingReviews"
                        x-cloak
                        class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-[1px] dark:bg-neutral-950/70"
                    >
                        <div class="flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-xs font-medium shadow-xs dark:border-neutral-800 dark:bg-neutral-900">
                            <x-lucide-loader-circle class="h-3.5 w-3.5 animate-spin" />
                            <span>Memuat riwayat reviu...</span>
                        </div>
                    </div>

                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr class="border-b border-neutral-200 bg-neutral-50/70 text-[11px] uppercase tracking-wider text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900/50 dark:text-neutral-400">
                                <th class="whitespace-nowrap px-3 py-2.5 text-center font-medium w-10">#</th>
                                <th class="whitespace-nowrap px-3 py-2.5 font-medium w-36">WAKTU</th>
                                <th class="whitespace-nowrap px-3 py-2.5 font-medium w-40">REVIEWER</th>
                                <th class="whitespace-nowrap px-3 py-2.5 text-center font-medium w-28">STATUS</th>
                                <th class="px-3 py-2.5 font-medium">CATATAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <template x-for="(log, idx) in reviewLogs" :key="log.id">
                                <tr class="hover:bg-neutral-50/60 dark:hover:bg-neutral-900/30">
                                    <td class="whitespace-nowrap px-3 py-2.5 text-center text-neutral-500" x-text="idx + 1"></td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-neutral-700 dark:text-neutral-300" x-text="log.created_at"></td>
                                    <td class="whitespace-nowrap px-3 py-2.5 font-medium text-neutral-900 dark:text-neutral-100" x-text="log.reviewer_name"></td>
                                    <td class="whitespace-nowrap px-3 py-2.5 text-center">
                                        <template x-if="log.status === 'Disetujui'">
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                Disetujui
                                            </span>
                                        </template>
                                        <template x-if="log.status === 'Koreksi'">
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                                Koreksi
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2.5 text-neutral-800 dark:text-neutral-200 break-words" x-text="log.catatan || '-'"></td>
                                </tr>
                            </template>

                            <template x-if="!loadingReviews && reviewLogs.length === 0">
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                                        Belum ada riwayat reviu untuk Sprin ini.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end border-t border-neutral-200 px-6 py-3.5 dark:border-neutral-800">
            <button
                type="button"
                x-on:click="closeReviewModal()"
                class="inline-flex h-9 items-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
