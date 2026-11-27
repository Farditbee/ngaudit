@php
    $noKka = $kka->nomor_kka ?: ('KKA '.$kka->tanggal_kka?->format('d/m/Y'));
    $inputClass = 'block h-9 w-full rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 shadow-xs placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/20 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100';
    $readonlyClass = 'flex min-h-9 items-center rounded-md border border-neutral-200 bg-neutral-100 px-3 py-1.5 text-xs font-medium text-neutral-800 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-200';
    $pageTitle = 'Tabulasi '.$jenisTabulasi;
@endphp

<x-admin-layout :breadcrumbs="['Audit', 'Pelaksanaan', 'KKA', $pageTitle]">
    <x-slot:title>{{ $pageTitle }} KKA</x-slot:title>

    <div
        x-data="kkaTabulasiDataTable({{ Js::from($tableConfig) }})"
        class="space-y-6"
    >
        {{-- Top Page Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Kertas Kerja Audit (KKA)</p>
                <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">
                    {{ $pageTitle }} pada KKA nomor : <span class="text-emerald-600 dark:text-emerald-400">{{ $noKka }}</span>
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('audit.kka.index') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md bg-blue-600 px-4 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700"
                >
                    <x-lucide-arrow-left class="h-3.5 w-3.5" />
                    <span>Kembali ke KKA</span>
                </a>
            </div>
        </div>

        {{-- Navigasi Tab Jenis Tabulasi --}}
        <div class="border-b border-neutral-200 dark:border-neutral-800">
            <nav class="-mb-px flex flex-wrap gap-2" aria-label="Tabs">
                @foreach ([
                    'tabulasi' => ['label' => 'Tabulasi Temuan', 'jenis' => 'Temuan'],
                    'konsultasi' => ['label' => 'Tabulasi Konsultasi', 'jenis' => 'Konsultasi'],
                    'positif' => ['label' => 'Tabulasi Positif', 'jenis' => 'Positif'],
                ] as $slug => $tabInfo)
                    @php
                        $isActiveTab = $tabSlug === $slug;
                    @endphp
                    <a
                        href="{{ route('audit.kka.tabulasi.index', [$kka, $slug]) }}"
                        class="inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-xs font-semibold transition-colors {{ $isActiveTab
                            ? 'border-neutral-900 text-neutral-900 dark:border-white dark:text-white'
                            : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-800 dark:text-neutral-400 dark:hover:border-neutral-700 dark:hover:text-neutral-200' }}"
                    >
                        <span>{{ $tabInfo['label'] }}</span>
                        @if ($tabInfo['jenis'] === 'Temuan')
                            <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isActiveTab
                                ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900'
                                : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300' }}">
                                {{ $tabCounts['Temuan'] ?? 0 }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- DataTable Tabulasi (Fixed tanpa scroll samping & tombol Tambah di posisi search) --}}
        <x-data-table
            :fixed="true"
            emptyMessage="Belum ada data {{ strtolower($pageTitle) }} untuk KKA ini. Klik tombol Tambah {{ $pageTitle }} untuk menambahkan data baru."
        >
            <x-slot:toolbarRight>
                <button
                    type="button"
                    x-on:click="openCreateModal()"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md bg-neutral-900 px-4 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    <x-lucide-plus class="h-3.5 w-3.5" />
                    <span>Tambah {{ $pageTitle }}</span>
                </button>
            </x-slot:toolbarRight>
            <template x-for="(row, rIndex) in rows" :key="row.id">
                <tr
                    class="border-b border-neutral-200 align-top transition-colors hover:bg-neutral-100/60 last:border-b-0 dark:border-neutral-800 dark:hover:bg-neutral-800/40"
                    :class="rIndex % 2 === 0 ? 'bg-neutral-50/50 dark:bg-neutral-900/20' : 'bg-white dark:bg-neutral-950'"
                >
                    {{-- 1. # --}}
                    <td class="border-r border-neutral-200 px-1.5 py-2 text-center text-[11px] text-neutral-600 dark:border-neutral-800 dark:text-neutral-400" x-text="row.DT_RowIndex"></td>

                    {{-- 2. No --}}
                    <td class="break-words border-r border-neutral-200 px-1 py-2 text-center text-[11px] font-semibold text-neutral-900 dark:border-neutral-800 dark:text-neutral-100" x-text="row.nomor"></td>

                    @if ($jenisTabulasi === 'Temuan')
                        {{-- 3. Temuan --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_temuan || '-'"></div>
                        </td>

                        {{-- 4. Kode Temuan --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-1.5 py-2 text-[11px] leading-snug text-neutral-900 dark:border-neutral-800 dark:text-neutral-100">
                            <template x-if="row.kode_temuan_kode || row.sub_kode_temuan_kode">
                                <div class="space-y-0.5">
                                    <div class="font-bold" x-text="row.kode_temuan_kode || '-'"></div>
                                    <template x-if="row.sub_kode_temuan_kode">
                                        <div class="text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                            Sub kode : <span class="font-semibold text-neutral-900 dark:text-neutral-100" x-text="row.sub_kode_temuan_kode"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!row.kode_temuan_kode && !row.sub_kode_temuan_kode">
                                <span>-</span>
                            </template>
                        </td>

                        {{-- 5. Nilai Temuan --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-1.5 py-2 text-[11px] font-semibold leading-tight text-emerald-700 dark:border-neutral-800 dark:text-emerald-400" x-text="row.nilai_temuan_formatted"></td>

                        {{-- 6. Kriteria --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_kriteria || '-'"></div>
                        </td>

                        {{-- 7. Sebab --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_sebab || '-'"></div>
                        </td>

                        {{-- 8. Akibat --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_akibat || '-'"></div>
                        </td>

                        {{-- 9. Rekomendasi --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_rekomendasi || '-'"></div>
                        </td>
                    @elseif ($jenisTabulasi === 'Konsultasi')
                        {{-- 3. Uraian Permasalahan --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_temuan || '-'"></div>
                        </td>

                        {{-- 4. Kriteria --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_kriteria || '-'"></div>
                        </td>

                        {{-- 5. Rekomendasi --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-2.5 py-2 text-[11px] leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-[11px] leading-snug dark:prose-invert" x-html="row.uraian_rekomendasi || '-'"></div>
                        </td>
                    @else
                        {{-- 3. Deskripsi (Positif) --}}
                        <td class="whitespace-normal break-words border-r border-neutral-200 px-3 py-2 text-xs leading-snug text-neutral-800 dark:border-neutral-800 dark:text-neutral-200">
                            <div class="prose prose-xs max-w-none break-words text-xs leading-snug dark:prose-invert" x-html="row.uraian_temuan || '-'"></div>
                        </td>
                    @endif

                    {{-- Status --}}
                    <td class="border-r border-neutral-200 px-2 py-2.5 text-center dark:border-neutral-800">
                        <div class="inline-flex flex-col items-center justify-center gap-1.5">
                            <button
                                type="button"
                                x-on:click.stop="toggleRowStatus(row)"
                                x-bind:disabled="row.updatingStatus === true"
                                role="switch"
                                :aria-checked="row.status === 'Final'"
                                class="relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:opacity-50"
                                :class="row.status === 'Final' ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-700'"
                            >
                                <span
                                    class="pointer-events-none inline-flex h-5 w-5 transform items-center justify-center rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                    :class="row.status === 'Final' ? 'translate-x-6' : 'translate-x-0'"
                                ></span>
                            </button>
                            <span
                                class="text-xs font-bold leading-none"
                                :class="row.status === 'Final' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                                x-text="row.status || 'Draft'"
                            ></span>
                        </div>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-1.5 py-2 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <button type="button" x-on:click="openEditModal(row)" class="inline-flex h-6 w-6 items-center justify-center rounded border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800" title="Ubah Tabulasi">
                                <x-lucide-pencil class="h-3 w-3" />
                            </button>
                            <button
                                type="button"
                                x-on:click="confirmDelete(row)"
                                class="inline-flex h-6 w-6 items-center justify-center rounded border border-red-200 bg-white text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Hapus Tabulasi"
                            >
                                <x-lucide-trash-2 class="h-3 w-3" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </x-data-table>

        {{-- Modal Tambah / Edit Tabulasi --}}
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
                class="relative z-10 my-6 w-full max-w-5xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-950"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/70 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/50">
                    <div>
                        <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100" x-text="(editingId ? 'Ubah ' : 'Tambah ') + 'Tabulasi ' + form.jenis_tabulasi"></h2>
                        <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
                            Lengkapi formulir tabulasi KKA beserta auditor pelaksana dan statusnya.
                        </p>
                    </div>
                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    >
                        <x-lucide-x class="h-4 w-4" />
                    </button>
                </div>

                <form x-on:submit.prevent="saveTabulasi()">
                    <div x-ref="modalScrollArea" class="max-h-[78vh] space-y-6 overflow-y-auto px-6 py-5">
                        <template x-if="errors.general">
                            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                                <p class="font-semibold" x-text="errors.general[0]"></p>
                            </div>
                        </template>

                        {{-- 1 - 4. Jenis Tabulasi, Bidang (Locked), Nomor, Tipe Temuan (hanya jika Temuan) --}}
                        <div class="grid gap-4" :class="form.jenis_tabulasi === 'Temuan' ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-3'">
                            {{-- 1. Jenis Tabulasi --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    1. Jenis Tabulasi <span class="text-red-500">*</span>
                                </label>
                                <select x-model="form.jenis_tabulasi" x-on:change="onJenisTabulasiChange()" class="{{ $inputClass }}">
                                    <option value="Temuan">Tabulasi Temuan</option>
                                    <option value="Konsultasi">Tabulasi Konsultasi</option>
                                    <option value="Positif">Tabulasi Positif</option>
                                </select>
                                <template x-if="errors.jenis_tabulasi">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.jenis_tabulasi[0]"></p>
                                </template>
                            </div>

                            {{-- 2. Bidang (Otomatis Lock) --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    2. Bidang (Terkunci)
                                </label>
                                <div class="{{ $readonlyClass }}">
                                    <x-lucide-lock class="mr-1.5 h-3.5 w-3.5 shrink-0 text-neutral-400" />
                                    <span x-text="bidang || '-'"></span>
                                </div>
                            </div>

                            {{-- 3. Nomor --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    3. Nomor <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    x-model="form.nomor"
                                    placeholder="Contoh: 01"
                                    class="{{ $inputClass }}"
                                />
                                <template x-if="errors.nomor">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.nomor[0]"></p>
                                </template>
                            </div>

                            {{-- 4. Tipe Temuan (Khusus Tabulasi Temuan) --}}
                            <div x-show="form.jenis_tabulasi === 'Temuan'" class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    4. Tipe Temuan
                                </label>
                                <select x-model="form.tipe_temuan" class="{{ $inputClass }}">
                                    <option value="">-- Pilih Tipe Temuan --</option>
                                    <template x-for="tt in tipeTemuans" :key="tt">
                                        <option :value="tt" x-text="tt"></option>
                                    </template>
                                </select>
                                <template x-if="errors.tipe_temuan">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.tipe_temuan[0]"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Bagian I: Uraian Temuan / Uraian Permasalahan / Deskripsi --}}
                        <div class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/40 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2.5 dark:border-neutral-800">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-200" x-text="form.jenis_tabulasi === 'Temuan' ? 'Bagian I — Kode & Uraian Temuan' : (form.jenis_tabulasi === 'Konsultasi' ? 'Bagian I — Uraian Permasalahan' : 'Bagian I — Deskripsi Positif')"></h3>
                                <span x-show="form.jenis_tabulasi === 'Temuan'" class="text-[11px] text-neutral-500 dark:text-neutral-400">Memilih kode/sub kode otomatis mengisi Uraian Temuan</span>
                            </div>

                            {{-- Kode Temuan & Sub Kode Temuan hanya untuk Tabulasi Temuan --}}
                            <div x-show="form.jenis_tabulasi === 'Temuan'" class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="5. Kode Temuan"
                                        placeholder="-- Pilih Kode Temuan --"
                                        itemsExpr="kodeTemuanOptions"
                                        valueModel="form.kode_temuan_id"
                                        changeAction="onKodeTemuanChange()"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.kode_temuan_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode_temuan_id[0]"></p>
                                    </template>
                                </div>

                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="6. Sub Kode Temuan"
                                        placeholder="-- Pilih Sub Kode Temuan --"
                                        itemsExpr="subKodeTemuanOptions"
                                        valueModel="form.sub_kode_temuan_id"
                                        changeAction="onSubKodeTemuanChange()"
                                        disabledExpr="!form.kode_temuan_id"
                                        disabledMessage="Pilih Kode Temuan terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.sub_kode_temuan_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.sub_kode_temuan_id[0]"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- Nilai Temuan (IDR) hanya tampil jika Tabulasi Temuan & Kode Temuan 01, 02, 03, 05 --}}
                            <div x-show="form.jenis_tabulasi === 'Temuan' && requiresNilaiTemuan" x-cloak x-transition class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                                <label class="mb-1.5 block text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                                    Nilai Temuan (IDR)
                                </label>
                                <div class="flex rounded-md shadow-xs">
                                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-neutral-200 bg-neutral-100 px-3 text-xs font-semibold text-neutral-700 dark:border-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">
                                        IDR
                                    </span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        :value="formatRupiahInput(form.nilai_temuan)"
                                        x-on:input="onNilaiTemuanInput($event)"
                                        placeholder="Contoh: 125.000.000"
                                        class="block h-9 w-full rounded-r-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100"
                                    />
                                </div>
                                <template x-if="errors.nilai_temuan">
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errors.nilai_temuan[0]"></p>
                                </template>
                            </div>

                            {{-- Editor Uraian Temuan / Uraian Permasalahan / Deskripsi --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300" x-text="form.jenis_tabulasi === 'Temuan' ? '7. Uraian Temuan' : (form.jenis_tabulasi === 'Konsultasi' ? '4. Uraian Permasalahan' : '4. Deskripsi')"></label>
                                <div x-on:mousedown="activeTab = 'uraian_temuan'" class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                    <x-tiptap-toolbar />
                                    <div
                                        x-ref="editor_uraian_temuan"
                                        data-tabulasi-editor="uraian_temuan"
                                        x-on:click="setActiveEditor('uraian_temuan', $event)"
                                        class="tiptap max-h-[300px] min-h-[160px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
                                    ></div>
                                </div>
                                <template x-if="errors.uraian_temuan">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian_temuan[0]"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Bagian Kriteria (Tampil untuk Temuan dan Konsultasi) --}}
                        <div x-show="form.jenis_tabulasi !== 'Positif'" class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/40 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2.5 dark:border-neutral-800">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-200">
                                    Bagian II — Kriteria Pemeriksaan
                                </h3>
                                <span class="text-[11px] text-neutral-500 dark:text-neutral-400">Filter kriteria berdasarkan bidang lalu klik Tambahkan Kriteria</span>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-12">
                                <div class="sm:col-span-4 space-y-1.5">
                                    <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-400">Filter Bidang Kriteria</label>
                                    <select x-model="kriteriaBidangFilter" x-on:change="onKriteriaFilterChange()" class="{{ $inputClass }}">
                                        <option value="">Semua Bidang</option>
                                        <option value="is_opsnal">Operasional (Opsnal)</option>
                                        <option value="is_logistik">Logistik</option>
                                        <option value="is_sdm">SDM</option>
                                        <option value="is_garkeu">Anggaran & Keuangan (Garkeu)</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-8 space-y-1.5">
                                    <x-searchable-select
                                        label="Pilih Kriteria"
                                        placeholder="-- Cari dan Pilih Kriteria --"
                                        itemsExpr="filteredKriteriaOptions"
                                        valueModel="form.kriteria_id"
                                        changeAction=""
                                        :wrap="true"
                                    />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    x-on:click="appendSelectedKriteria()"
                                    x-bind:disabled="!form.kriteria_id"
                                    class="inline-flex h-8 items-center gap-1.5 rounded-md bg-blue-600 px-3.5 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-blue-700 disabled:opacity-50"
                                >
                                    <x-lucide-plus class="h-3.5 w-3.5" />
                                    <span>Tambahkan Kriteria</span>
                                </button>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Uraian Kriteria
                                </label>
                                <div x-on:mousedown="activeTab = 'uraian_kriteria'" class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                    <x-tiptap-toolbar />
                                    <div
                                        x-ref="editor_uraian_kriteria"
                                        data-tabulasi-editor="uraian_kriteria"
                                        x-on:click="setActiveEditor('uraian_kriteria', $event)"
                                        class="tiptap max-h-[300px] min-h-[160px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
                                    ></div>
                                </div>
                                <template x-if="errors.uraian_kriteria">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian_kriteria[0]"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Bagian Sebab (Khusus Tabulasi Temuan) --}}
                        <div x-show="form.jenis_tabulasi === 'Temuan'" class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/40 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2.5 dark:border-neutral-800">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-200">
                                    Bagian III — Kode & Uraian Sebab
                                </h3>
                                <span class="text-[11px] text-neutral-500 dark:text-neutral-400">Tergantung pada Kode Temuan yang dipilih</span>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="Kode Sebab"
                                        placeholder="-- Pilih Kode Sebab --"
                                        itemsExpr="kodeSebabOptions"
                                        valueModel="form.kode_sebab_id"
                                        changeAction="onKodeSebabChange()"
                                        disabledExpr="!form.kode_temuan_id"
                                        disabledMessage="Pilih Kode Temuan terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.kode_sebab_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode_sebab_id[0]"></p>
                                    </template>
                                </div>

                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="Sub Kode Sebab"
                                        placeholder="-- Pilih Sub Kode Sebab --"
                                        itemsExpr="subKodeSebabOptions"
                                        valueModel="form.sub_kode_sebab_id"
                                        changeAction="onSubKodeSebabChange()"
                                        disabledExpr="!form.kode_sebab_id"
                                        disabledMessage="Pilih Kode Sebab terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.sub_kode_sebab_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.sub_kode_sebab_id[0]"></p>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Uraian Kode Sebab
                                </label>
                                <div x-on:mousedown="activeTab = 'uraian_sebab'" class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                    <x-tiptap-toolbar />
                                    <div
                                        x-ref="editor_uraian_sebab"
                                        data-tabulasi-editor="uraian_sebab"
                                        x-on:click="setActiveEditor('uraian_sebab', $event)"
                                        class="tiptap max-h-[300px] min-h-[160px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
                                    ></div>
                                </div>
                                <template x-if="errors.uraian_sebab">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian_sebab[0]"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Bagian Akibat (Khusus Tabulasi Temuan) --}}
                        <div x-show="form.jenis_tabulasi === 'Temuan'" class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/40 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2.5 dark:border-neutral-800">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-200">
                                    Bagian IV — Kode & Uraian Akibat
                                </h3>
                                <span class="text-[11px] text-neutral-500 dark:text-neutral-400">Tergantung pada Kode Temuan yang dipilih</span>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="Kode Akibat"
                                        placeholder="-- Pilih Kode Akibat --"
                                        itemsExpr="kodeAkibatOptions"
                                        valueModel="form.kode_akibat_id"
                                        changeAction="onKodeAkibatChange()"
                                        disabledExpr="!form.kode_temuan_id"
                                        disabledMessage="Pilih Kode Temuan terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.kode_akibat_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode_akibat_id[0]"></p>
                                    </template>
                                </div>

                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="Sub Kode Akibat"
                                        placeholder="-- Pilih Sub Kode Akibat --"
                                        itemsExpr="subKodeAkibatOptions"
                                        valueModel="form.sub_kode_akibat_id"
                                        changeAction="onSubKodeAkibatChange()"
                                        disabledExpr="!form.kode_akibat_id"
                                        disabledMessage="Pilih Kode Akibat terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.sub_kode_akibat_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.sub_kode_akibat_id[0]"></p>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Uraian Kode Akibat
                                </label>
                                <div x-on:mousedown="activeTab = 'uraian_akibat'" class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                    <x-tiptap-toolbar />
                                    <div
                                        x-ref="editor_uraian_akibat"
                                        data-tabulasi-editor="uraian_akibat"
                                        x-on:click="setActiveEditor('uraian_akibat', $event)"
                                        class="tiptap max-h-[300px] min-h-[160px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
                                    ></div>
                                </div>
                                <template x-if="errors.uraian_akibat">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian_akibat[0]"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Bagian Rekomendasi (Tampil untuk Temuan dan Konsultasi) --}}
                        <div x-show="form.jenis_tabulasi !== 'Positif'" class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50/40 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2.5 dark:border-neutral-800">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-200">
                                    Kode & Uraian Rekomendasi
                                </h3>
                                <span class="text-[11px] text-neutral-500 dark:text-neutral-400" x-text="form.jenis_tabulasi === 'Temuan' ? 'Tergantung pada Kode Temuan yang dipilih' : 'Menampilkan seluruh daftar Kode Rekomendasi'"></span>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="Kode Rekomendasi"
                                        placeholder="-- Pilih Kode Rekomendasi --"
                                        itemsExpr="kodeRekomendasiOptions"
                                        valueModel="form.kode_rekomendasi_id"
                                        changeAction="onKodeRekomendasiChange()"
                                        disabledExpr="form.jenis_tabulasi === 'Temuan' && !form.kode_temuan_id"
                                        disabledMessage="Pilih Kode Temuan terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.kode_rekomendasi_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.kode_rekomendasi_id[0]"></p>
                                    </template>
                                </div>

                                <div class="space-y-1.5">
                                    <x-searchable-select
                                        label="Sub Kode Rekomendasi"
                                        placeholder="-- Pilih Sub Kode Rekomendasi --"
                                        itemsExpr="subKodeRekomendasiOptions"
                                        valueModel="form.sub_kode_rekomendasi_id"
                                        changeAction="onSubKodeRekomendasiChange()"
                                        disabledExpr="!form.kode_rekomendasi_id"
                                        disabledMessage="Pilih Kode Rekomendasi terlebih dahulu"
                                        :wrap="true"
                                    />
                                    <template x-if="errors.sub_kode_rekomendasi_id">
                                        <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.sub_kode_rekomendasi_id[0]"></p>
                                    </template>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Uraian Kode Rekomendasi
                                </label>
                                <div x-on:mousedown="activeTab = 'uraian_rekomendasi'" class="rounded-xl border border-neutral-200 bg-white shadow-xs dark:border-neutral-800 dark:bg-neutral-950">
                                    <x-tiptap-toolbar />
                                    <div
                                        x-ref="editor_uraian_rekomendasi"
                                        data-tabulasi-editor="uraian_rekomendasi"
                                        x-on:click="setActiveEditor('uraian_rekomendasi', $event)"
                                        class="tiptap max-h-[300px] min-h-[160px] cursor-text overflow-y-auto bg-white dark:bg-neutral-950"
                                    ></div>
                                </div>
                                <template x-if="errors.uraian_rekomendasi">
                                    <p class="text-xs text-red-600 dark:text-red-400" x-text="errors.uraian_rekomendasi[0]"></p>
                                </template>
                            </div>
                        </div>

                        {{-- Auditor & Status Toggle (Semua Jenis Tabulasi) --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                                <x-searchable-select
                                    label="Auditor"
                                    placeholder="-- Cari dan Pilih Auditor --"
                                    itemsExpr="auditors"
                                    valueModel="form.auditor_id"
                                    changeAction=""
                                />
                                <template x-if="errors.auditor_id">
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errors.auditor_id[0]"></p>
                                </template>
                            </div>

                            <div class="rounded-xl border border-neutral-200 bg-neutral-50/50 p-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                                <label class="mb-2 block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                    Status Tabulasi <span class="text-red-500">*</span>
                                </label>
                                <div class="inline-flex items-center gap-3">
                                    <span
                                        class="cursor-pointer select-none text-xs transition-colors"
                                        :class="form.status !== 'Final' ? 'font-bold text-amber-600 dark:text-amber-400' : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                        x-on:click="form.status = 'Draft'"
                                    >Draft</span>

                                    <button
                                        type="button"
                                        x-on:click="form.status = (form.status === 'Final' ? 'Draft' : 'Final')"
                                        role="switch"
                                        :aria-checked="form.status === 'Final'"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                        :class="form.status === 'Final' ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-700'"
                                    >
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                            :class="form.status === 'Final' ? 'translate-x-5' : 'translate-x-0'"
                                        ></span>
                                    </button>

                                    <span
                                        class="cursor-pointer select-none text-xs transition-colors"
                                        :class="form.status === 'Final' ? 'font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-400 hover:text-neutral-600 dark:text-neutral-500'"
                                        x-on:click="form.status = 'Final'"
                                    >Final</span>
                                </div>
                                <template x-if="errors.status">
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errors.status[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-900/30">
                        <button
                            type="button"
                            x-on:click="closeModal()"
                            class="inline-flex h-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-4 text-xs font-medium text-neutral-700 shadow-xs hover:bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-300 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            x-bind:disabled="saving"
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-neutral-900 px-5 text-xs font-medium text-white shadow-xs hover:bg-neutral-800 disabled:opacity-50 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                        >
                            <template x-if="saving">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/20 border-t-white dark:border-neutral-900/20 dark:border-t-neutral-900"></span>
                            </template>
                            <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Simpan Tabulasi')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
