<x-admin-layout :breadcrumbs="['Anev', $lingkup, 'Tabulasi Per Bidang']">
    <div
        class="space-y-6"
        x-data="{
            activeTahap: {{ $activeTahap }},
            lingkup: '{{ $lingkup }}',
            tahap1: @js($tahap1),
            tahap2: @js($tahap2),
            selectedSatker: null,
            hoveredSlice: null,
            _slices: [],
            setHovered(key) {
                if (this.hoveredSlice !== key) {
                    this.hoveredSlice = key;
                    this.drawChart();
                }
            },
            handleCanvasMouseMove(e) {
                const canvas = this.$refs.chartCanvas;
                if (!canvas) return;
                const rect = canvas.getBoundingClientRect();
                const scaleX = 400 / rect.width;
                const scaleY = 260 / rect.height;
                const mx = (e.clientX - rect.left) * scaleX;
                const my = (e.clientY - rect.top) * scaleY;

                const x0 = 200;
                const y0 = 120;
                const Rx = 105;
                const Ry = 95;
                const rx = 58;
                const ry = 52;
                const H = 7;

                const dx = mx - x0;
                const dy = my - y0;

                // Cek hit-area berbentuk elips luar dan elips dalam (termasuk kedalaman 3D)
                const dOuter = Math.pow(dx / (Rx + 16), 2) + Math.pow(dy / (Ry + H + 12), 2);
                const dInner = Math.pow(dx / (rx - 8), 2) + Math.pow(dy / (ry - 8), 2);

                if (dOuter <= 1.2 && dInner >= 0.8) {
                    let angle = Math.atan2(dy, dx);
                    if (angle < -Math.PI / 2) {
                        angle += 2 * Math.PI;
                    }
                    const s = (this._slices || []).find(sl => angle >= sl.start && angle < sl.end);
                    if (s && s.val > 0) {
                        this.setHovered(s.key);
                        canvas.style.cursor = 'pointer';
                        return;
                    }
                }
                this.setHovered(null);
                canvas.style.cursor = 'default';
            },
            get currentData() {
                return this.activeTahap === 1 ? this.tahap1 : this.tahap2;
            },
            openSubUnitModal(satker) {
                this.selectedSatker = satker;
            },
            closeSubUnitModal() {
                this.selectedSatker = null;
            },
            get chartData() {
                const rows = this.currentData.rows || [];
                const getVal = (name) => {
                    const r = rows.find(x => x.bidang === name);
                    return r ? (parseInt(r.total, 10) || 0) : 0;
                };
                const opsnal = getVal('OPERASIONAL');
                const sdm = getVal('SDM');
                const logistik = getVal('LOGISTIK');
                const garkeu = getVal('GARKEU');
                const total = opsnal + sdm + logistik + garkeu;

                const rawData = [
                    { key: 'OPERASIONAL', label: 'OPERASIONAL', val: opsnal, color: '#38bdf8', dark: '#0284c7', badgeClass: 'bg-sky-500' },
                    { key: 'SDM', label: 'SDM', val: sdm, color: '#34d399', dark: '#059669', badgeClass: 'bg-emerald-500' },
                    { key: 'LOGISTIK', label: 'LOGISTIK', val: logistik, color: '#fbbf24', dark: '#d97706', badgeClass: 'bg-amber-500' },
                    { key: 'GARKEU', label: 'GARKEU', val: garkeu, color: '#a78bfa', dark: '#7c3aed', badgeClass: 'bg-purple-500' },
                ];

                return {
                    total: total,
                    items: rawData.map(it => ({
                        ...it,
                        pct: total > 0 ? ((it.val / total) * 100).toFixed(1) : '0.0'
                    }))
                };
            },
            drawChart() {
                const canvas = this.$refs.chartCanvas;
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                const dpr = window.devicePixelRatio || 1;
                const w = 400;
                const h = 260;
                canvas.width = w * dpr;
                canvas.height = h * dpr;
                canvas.style.width = w + 'px';
                canvas.style.height = h + 'px';

                ctx.resetTransform();
                ctx.scale(dpr, dpr);
                ctx.clearRect(0, 0, w, h);

                const rows = this.currentData.rows || [];
                const getVal = (name) => {
                    const r = rows.find(x => x.bidang === name);
                    return r ? (parseInt(r.total, 10) || 0) : 0;
                };

                const opsnal = getVal('OPERASIONAL');
                const sdm = getVal('SDM');
                const logistik = getVal('LOGISTIK');
                const garkeu = getVal('GARKEU');
                const total = opsnal + sdm + logistik + garkeu;

                const rawData = [
                    { key: 'OPERASIONAL', label: 'OPERASIONAL', val: opsnal, color: '#38bdf8', dark: '#0284c7' },
                    { key: 'SDM', label: 'SDM', val: sdm, color: '#34d399', dark: '#059669' },
                    { key: 'LOGISTIK', label: 'LOGISTIK', val: logistik, color: '#fbbf24', dark: '#d97706' },
                    { key: 'GARKEU', label: 'GARKEU', val: garkeu, color: '#a78bfa', dark: '#7c3aed' },
                ];

                let curAngle = -Math.PI / 2;
                const slices = rawData.map(s => {
                    const frac = total > 0 ? (s.val / total) : 0.25;
                    const pct = total > 0 ? ((s.val / total) * 100).toFixed(1) : '0.0';
                    const angle = frac * 2 * Math.PI;
                    const start = curAngle;
                    const end = curAngle + angle;
                    curAngle += angle;
                    return { ...s, frac, pct, start, end };
                });
                this._slices = slices;

                const x0 = w / 2;
                const y0 = 120;
                const Rx = 105;
                const Ry = 95;
                const rx = 58;
                const ry = 52;
                const H = 7;

                // 1. Drop Shadow Donut Dasar
                ctx.save();
                ctx.beginPath();
                ctx.ellipse(x0, y0 + H + 6, Rx * 0.98, Ry * 0.95, 0, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(0, 0, 0, 0.07)';
                ctx.fill();
                ctx.restore();

                // Hitung offset timbul / elevasi 3D untuk irisan yang di-hover
                const sliceData = slices.map(s => {
                    const isHovered = this.hoveredSlice === s.key && s.val > 0;
                    const mid = (s.start + s.end) / 2;
                    const pushDist = isHovered ? 14 : 0;
                    const lift = isHovered ? 5 : 0;
                    const sx = Math.cos(mid) * pushDist;
                    const sy = Math.sin(mid) * (pushDist * 0.9);
                    return { ...s, isHovered, mid, sx, sy, lift };
                });

                // Shadow lantai khusus untuk potongan yang timbul
                const hoveredItem = sliceData.find(s => s.isHovered);
                if (hoveredItem) {
                    ctx.save();
                    ctx.beginPath();
                    ctx.ellipse(
                        x0 + hoveredItem.sx * 1.15,
                        y0 + hoveredItem.sy * 1.15 + H + 9,
                        (Rx - rx) * 0.65,
                        (Ry - ry) * 0.55,
                        hoveredItem.mid,
                        0,
                        Math.PI * 2
                    );
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                    ctx.fill();
                    ctx.restore();
                }

                // Helper untuk menggambar dinding ketebalan 3D (extrusion)
                const drawSliceExtrusion = (s) => {
                    if (s.frac <= 0) return;
                    const curH = H + s.lift;
                    for (let depth = curH; depth >= 1; depth -= 1) {
                        ctx.beginPath();
                        ctx.ellipse(x0 + s.sx, y0 + s.sy - s.lift + depth, Rx, Ry, 0, s.start, s.end);
                        ctx.ellipse(x0 + s.sx, y0 + s.sy - s.lift + depth, rx, ry, 0, s.end, s.start, true);
                        ctx.closePath();
                        ctx.fillStyle = s.dark;
                        ctx.fill();
                    }

                    // Jika di-hover dan timbul keluar, gambar dinding penampang radialnya agar tampak pejal 3D
                    if (s.isHovered) {
                        [s.start, s.end].forEach(a => {
                            ctx.beginPath();
                            ctx.moveTo(x0 + s.sx + rx * Math.cos(a), y0 + s.sy - s.lift + ry * Math.sin(a));
                            ctx.lineTo(x0 + s.sx + Rx * Math.cos(a), y0 + s.sy - s.lift + Ry * Math.sin(a));
                            ctx.lineTo(x0 + s.sx + Rx * Math.cos(a), y0 + s.sy + H + Ry * Math.sin(a));
                            ctx.lineTo(x0 + s.sx + rx * Math.cos(a), y0 + s.sy + H + ry * Math.sin(a));
                            ctx.closePath();
                            ctx.fillStyle = s.dark;
                            ctx.fill();
                        });
                    }
                };

                // Gambar lapisan extrusion: gambar slice normal dulu, slice yang timbul digambar paling atas
                sliceData.filter(s => !s.isHovered).forEach(drawSliceExtrusion);
                sliceData.filter(s => s.isHovered).forEach(drawSliceExtrusion);

                // Helper untuk menggambar permukaan atas (top face)
                const drawSliceTop = (s) => {
                    if (s.frac <= 0) return;
                    ctx.save();
                    if (s.isHovered) {
                        ctx.shadowColor = s.color;
                        ctx.shadowBlur = 10;
                    }
                    ctx.beginPath();
                    const scaleR = s.isHovered ? 1.03 : 1.0;
                    ctx.ellipse(x0 + s.sx, y0 + s.sy - s.lift, Rx * scaleR, Ry * scaleR, 0, s.start, s.end);
                    ctx.ellipse(x0 + s.sx, y0 + s.sy - s.lift, rx * (s.isHovered ? 0.97 : 1.0), ry * (s.isHovered ? 0.97 : 1.0), 0, s.end, s.start, true);
                    ctx.closePath();
                    ctx.fillStyle = s.color;
                    ctx.fill();

                    ctx.strokeStyle = '#ffffff';
                    ctx.lineWidth = s.isHovered ? 3 : 2;
                    ctx.stroke();
                    ctx.restore();
                };

                // Gambar permukaan atas: yang normal dulu, lalu yang timbul
                sliceData.filter(s => !s.isHovered).forEach(drawSliceTop);
                sliceData.filter(s => s.isHovered).forEach(drawSliceTop);

                // 4. Label Jumlah Temuan di Irisan
                sliceData.forEach(s => {
                    if (s.val > 0 && s.frac > 0.04) {
                        const midR_x = (Rx + rx) / 2;
                        const midR_y = (Ry + ry) / 2;
                        const lx = x0 + s.sx + midR_x * Math.cos(s.mid);
                        const ly = y0 + s.sy - s.lift + midR_y * Math.sin(s.mid);

                        ctx.save();
                        ctx.shadowColor = 'rgba(0,0,0,0.85)';
                        ctx.shadowBlur = s.isHovered ? 6 : 4;
                        ctx.fillStyle = '#ffffff';
                        ctx.font = s.isHovered ? 'bold 16px sans-serif' : 'bold 14px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(s.val, lx, ly);
                        ctx.restore();
                    }
                });

                // 5. Center Hollow Hole Badge
                const isDark = document.documentElement.classList.contains('dark');
                ctx.save();
                ctx.beginPath();
                ctx.ellipse(x0, y0, rx * 0.88, ry * 0.88, 0, 0, Math.PI * 2);
                ctx.fillStyle = isDark ? '#171717' : '#ffffff';
                ctx.shadowColor = 'rgba(0,0,0,0.15)';
                ctx.shadowBlur = 6;
                ctx.fill();
                ctx.strokeStyle = isDark ? '#404040' : '#e2e8f0';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                ctx.textAlign = 'center';
                if (hoveredItem && hoveredItem.val > 0) {
                    ctx.fillStyle = hoveredItem.color;
                    ctx.font = 'bold 8px sans-serif';
                    ctx.fillText(hoveredItem.label, x0, y0 - 11);

                    ctx.fillStyle = isDark ? '#ffffff' : '#0f172a';
                    ctx.font = 'bold 20px sans-serif';
                    ctx.fillText(hoveredItem.val, x0, y0 + 6);

                    ctx.fillStyle = isDark ? '#a3a3a3' : '#64748b';
                    ctx.font = 'bold 9px sans-serif';
                    ctx.fillText(hoveredItem.pct + '% TEMUAN', x0, y0 + 20);
                } else {
                    ctx.fillStyle = isDark ? '#a3a3a3' : '#64748b';
                    ctx.font = 'bold 8px sans-serif';
                    ctx.fillText('TOTAL', x0, y0 - 10);

                    ctx.fillStyle = isDark ? '#ffffff' : '#0f172a';
                    ctx.font = 'bold 18px sans-serif';
                    ctx.fillText(total, x0, y0 + 6);

                    ctx.fillStyle = isDark ? '#a3a3a3' : '#64748b';
                    ctx.font = 'bold 8px sans-serif';
                    ctx.fillText('TEMUAN', x0, y0 + 19);
                }
                ctx.restore();
            }
        }"
        x-init="
            $nextTick(() => drawChart());
            $watch('activeTahap', () => $nextTick(() => drawChart()));
            $watch('currentData', () => $nextTick(() => drawChart()));
        "
    >
        {{-- Filter Data Container Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 border-b border-neutral-200 pb-3 dark:border-neutral-800">
                Filter Data
            </h3>

            <form method="GET" action="{{ url()->current() }}" class="mt-4">
                <input type="hidden" name="tahap" :value="activeTahap" />

                <div class="flex flex-wrap items-end gap-4">
                    {{-- Dari --}}
                    <div class="w-full sm:w-44">
                        <label for="filter_dari" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Dari
                        </label>
                        <input
                            type="date"
                            id="filter_dari"
                            name="dari"
                            value="{{ $filters['dari'] ?? '' }}"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs text-neutral-800 focus:border-sky-500 focus:outline-hidden focus:ring-2 focus:ring-sky-200 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:focus:ring-sky-800"
                        />
                    </div>

                    {{-- Sampai --}}
                    <div class="w-full sm:w-44">
                        <label for="filter_sampai" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Sampai
                        </label>
                        <input
                            type="date"
                            id="filter_sampai"
                            name="sampai"
                            value="{{ $filters['sampai'] ?? '' }}"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs text-neutral-800 focus:border-sky-500 focus:outline-hidden focus:ring-2 focus:ring-sky-200 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:focus:ring-sky-800"
                        />
                    </div>

                    {{-- Audit (Jenis Audit) --}}
                    <div class="w-full sm:w-56">
                        <label for="filter_jenis_audit" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Audit
                        </label>
                        <select
                            id="filter_jenis_audit"
                            name="jenis_audit"
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs text-neutral-800 focus:border-sky-500 focus:outline-hidden focus:ring-2 focus:ring-sky-200 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:focus:ring-sky-800"
                        >
                            <option value="">Semua</option>
                            <option value="Audit Kinerja" @selected(($filters['jenis_audit'] ?? '') === 'Audit Kinerja' || ($filters['jenis_audit'] ?? '') === 'Kinerja')>Audit Kinerja</option>
                            <option value="Audit PNBP" @selected(($filters['jenis_audit'] ?? '') === 'Audit PNBP' || ($filters['jenis_audit'] ?? '') === 'PNBP')>Audit PNBP</option>
                            <option value="Audit Tujuan Tertentu" @selected(($filters['jenis_audit'] ?? '') === 'Audit Tujuan Tertentu' || ($filters['jenis_audit'] ?? '') === 'Tujuan Tertentu')>Audit Tujuan Tertentu</option>
                        </select>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-sky-700 focus:outline-hidden focus:ring-2 focus:ring-sky-500 transition-colors cursor-pointer"
                        >
                            Filter
                        </button>
                        @if (! empty($filters['dari']) || ! empty($filters['sampai']) || ! empty($filters['jenis_audit']))
                            <a
                                href="{{ url()->current() }}"
                                class="inline-flex items-center justify-center rounded-lg bg-neutral-100 px-4 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 transition-colors"
                            >
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Main Container Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-2xs dark:border-neutral-800 dark:bg-neutral-950">
            {{-- Tabs: PER BIDANG TAHAP 1 & PER BIDANG TAHAP 2 --}}
            <div class="flex items-center gap-2 border-b border-neutral-200 pb-4 dark:border-neutral-800">
                <button
                    type="button"
                    x-on:click="activeTahap = 1"
                    class="rounded-md px-4 py-2 text-xs uppercase tracking-wider transition-all"
                    :class="activeTahap === 1
                        ? 'border border-neutral-300 bg-white font-bold text-neutral-800 shadow-2xs dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100'
                        : 'font-semibold text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200'"
                >
                    PER BIDANG TAHAP 1
                </button>

                <button
                    type="button"
                    x-on:click="activeTahap = 2"
                    class="rounded-md px-4 py-2 text-xs uppercase tracking-wider transition-all"
                    :class="activeTahap === 2
                        ? 'border border-neutral-300 bg-white font-bold text-neutral-800 shadow-2xs dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100'
                        : 'font-semibold text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200'"
                >
                    PER BIDANG TAHAP 2
                </button>
            </div>

            {{-- Centered Header: REKAPITULASI JUMLAH TEMUAN --}}
            <div class="my-6 text-center space-y-1">
                <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-100 underline decoration-1 underline-offset-4">
                    REKAPITULASI JUMLAH TEMUAN
                </h2>
                <div
                    class="text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 underline decoration-1 underline-offset-4"
                    x-text="currentData.tahap_roman"
                ></div>
                <div
                    class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 underline decoration-1 underline-offset-4"
                    x-text="currentData.date_range"
                ></div>
            </div>

            {{-- Subtitle on Left --}}
            <h3
                class="mb-3 text-xs sm:text-sm font-bold text-neutral-800 dark:text-neutral-200"
                x-text="'Rekap Status Per Bidang - Tahap ' + activeTahap"
            ></h3>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-sky-300 text-xs dark:border-neutral-700">
                    <thead>
                        <tr class="bg-neutral-50/80 text-center font-bold tracking-wider text-neutral-600 dark:bg-neutral-900/60 dark:text-neutral-300">
                            <th rowspan="2" class="border border-sky-300 px-4 py-3 align-middle dark:border-neutral-700 w-1/4 text-center">
                                BIDANG
                            </th>
                            <th colspan="2" class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700">
                                TINDAK LANJUT
                            </th>
                            <th colspan="2" class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700">
                                STATUS UJI NILAI
                            </th>
                            <th rowspan="2" class="border border-sky-300 px-4 py-3 align-middle dark:border-neutral-700 w-1/6 text-center">
                                TOTAL TEMUAN
                            </th>
                        </tr>
                        <tr class="bg-neutral-50/80 text-center font-bold tracking-wider text-neutral-600 dark:bg-neutral-900/60 dark:text-neutral-300">
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700">
                                BELUM
                            </th>
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700">
                                SUDAH
                            </th>
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700">
                                KOREKSI
                            </th>
                            <th class="border border-sky-300 px-4 py-2 align-middle text-emerald-600 dark:border-neutral-700 dark:text-emerald-400">
                                SELESAI
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-neutral-800 dark:text-neutral-200">
                        <template x-for="row in currentData.rows" :key="row.bidang">
                            <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/40">
                                <td class="border border-sky-300 px-4 py-3 font-bold uppercase text-neutral-900 dark:border-neutral-700 dark:text-neutral-100" x-text="row.bidang"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.belum"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.sudah"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.koreksi ?? row.belum_selesai"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-bold text-emerald-600 dark:border-neutral-700 dark:text-emerald-400" x-text="row.selesai"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-bold text-neutral-900 dark:border-neutral-700 dark:text-neutral-100" x-text="row.total"></td>
                            </tr>
                        </template>

                        {{-- Total Highlight Row --}}
                        <tr class="bg-[#fef3c7] font-bold text-neutral-900 dark:bg-amber-950/40 dark:text-amber-100">
                            <td class="border border-sky-300 px-4 py-3 font-bold uppercase dark:border-neutral-700">
                                TOTAL
                            </td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.total.belum"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.total.sudah"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.total.koreksi ?? currentData.total.belum_selesai"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold text-emerald-600 dark:border-neutral-700 dark:text-emerald-400" x-text="currentData.total.selesai"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.total.total"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- 3D Donut Chart: Proporsi Temuan Per Bidang --}}
            <div class="my-8 rounded-xl border border-neutral-200 bg-neutral-50/50 p-6 shadow-2xs dark:border-neutral-800 dark:bg-neutral-900/40">
                <div class="mb-4 text-center">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-200">
                        DIAGRAM TEMUAN PER BIDANG
                    </h4>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5" x-text="'Proporsi 4 Bidang Manajemen - Tahap ' + activeTahap"></p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 items-center gap-6">
                    {{-- 3D Donut Chart (Canvas) --}}
                    <div class="lg:col-span-7 flex flex-col items-center justify-center py-2">
                        <canvas
                            x-ref="chartCanvas"
                            class="w-full max-w-[400px] h-[260px] cursor-pointer"
                            @mousemove="handleCanvasMouseMove($event)"
                            @mouseleave="setHovered(null)"
                        ></canvas>
                    </div>

                    {{-- Legend Cards with Findings Count & Percentage --}}
                    <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
                        <template x-for="item in chartData.items" :key="'card-' + item.key">
                            <div
                                class="flex items-center justify-between rounded-lg border p-3 transition-all duration-200 cursor-pointer"
                                :class="hoveredSlice === item.key
                                    ? 'border-sky-500 bg-sky-50/80 shadow-md ring-2 ring-sky-300 dark:border-sky-500 dark:bg-sky-950/50 dark:ring-sky-700 scale-[1.02] -translate-y-0.5'
                                    : 'border-neutral-200 bg-white shadow-2xs dark:border-neutral-800 dark:bg-neutral-950 hover:border-neutral-300 dark:hover:border-neutral-700'"
                                @mouseenter="setHovered(item.key)"
                                @mouseleave="setHovered(null)"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="h-4 w-4 rounded-full shrink-0 shadow-xs" :style="`background-color: ${item.color};`"></span>
                                    <div>
                                        <span class="block text-xs font-bold text-neutral-800 dark:text-neutral-200" x-text="item.label"></span>
                                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400" x-text="item.pct + '% dari total temuan'"></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold" :class="item.badgeClass + ' text-white'" x-text="item.val + ' Temuan'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Table 2: TEMUAN BIDANG JEMEN PER SATKER (DINAS / KECAMATAN) --}}
            <div class="mt-8 overflow-x-auto">
                <table class="w-full border-collapse border border-sky-300 text-xs dark:border-neutral-700">
                    <thead>
                        <tr class="bg-neutral-50/80 text-center font-bold tracking-wider text-neutral-600 dark:bg-neutral-900/60 dark:text-neutral-300">
                            <th rowspan="2" class="border border-sky-300 px-3 py-3 align-middle dark:border-neutral-700 w-12 text-center">
                                NO
                            </th>
                            <th rowspan="2" class="border border-sky-300 px-4 py-3 align-middle dark:border-neutral-700 text-left">
                                <span x-text="lingkup === 'Dinas' ? 'DINAS' : 'KECAMATAN'"></span>
                            </th>
                            <th colspan="4" class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 text-center">
                                BIDANG
                            </th>
                            <th rowspan="2" class="border border-sky-300 px-4 py-3 align-middle dark:border-neutral-700 w-24 text-center">
                                JUMLAH
                            </th>
                        </tr>
                        <tr class="bg-neutral-50/80 text-center font-bold tracking-wider text-neutral-600 dark:bg-neutral-900/60 dark:text-neutral-300">
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-24">
                                OPSNAL
                            </th>
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-24">
                                SDM
                            </th>
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-24">
                                LOGISTIK
                            </th>
                            <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-24">
                                GARKEU
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-neutral-800 dark:text-neutral-200">
                        <template x-for="row in currentData.satker_rows" :key="row.satker">
                            <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/40">
                                <td class="border border-sky-300 px-3 py-3 text-center font-medium dark:border-neutral-700" x-text="row.no"></td>
                                <td class="border border-sky-300 px-4 py-3 dark:border-neutral-700">
                                    <button
                                        type="button"
                                        class="group flex w-full cursor-pointer items-center justify-between text-left font-semibold uppercase text-sky-700 hover:text-sky-900 hover:underline dark:text-sky-400 dark:hover:text-sky-200 transition-colors"
                                        x-on:click="openSubUnitModal(row)"
                                        :title="'Klik untuk melihat rincian sub unit ' + row.satker"
                                    >
                                        <span x-text="row.satker"></span>
                                        <svg class="ml-1 h-3.5 w-3.5 shrink-0 opacity-0 group-hover:opacity-100 text-sky-600 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.opsnal"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.sdm"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.logistik"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="row.garkeu"></td>
                                <td class="border border-sky-300 px-4 py-3 text-center font-bold text-neutral-900 dark:border-neutral-700 dark:text-neutral-100" x-text="row.jumlah"></td>
                            </tr>
                        </template>

                        <template x-if="!currentData.satker_rows || currentData.satker_rows.length === 0">
                            <tr>
                                <td colspan="7" class="border border-sky-300 px-4 py-6 text-center text-neutral-500 italic dark:border-neutral-700">
                                    Tidak ada data untuk tahap ini.
                                </td>
                            </tr>
                        </template>

                        {{-- Total Highlight Row --}}
                        <tr class="bg-[#fef3c7] font-bold text-neutral-900 dark:bg-amber-950/40 dark:text-amber-100">
                            <td colspan="2" class="border border-sky-300 px-4 py-3 text-center font-bold uppercase dark:border-neutral-700">
                                TOTAL
                            </td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.satker_total.opsnal"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.satker_total.sdm"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.satker_total.logistik"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.satker_total.garkeu"></td>
                            <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="currentData.satker_total.jumlah"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Popup Sub Unit --}}
        <div
            x-show="selectedSatker !== null"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-neutral-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:keydown.escape.window="closeSubUnitModal()"
        >
            {{-- Modal Dialog Panel --}}
            <div
                class="relative w-full max-w-4xl rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-neutral-800 dark:bg-neutral-900 p-6 max-h-[90vh] flex flex-col"
                x-on:click.outside="closeSubUnitModal()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Modal Header --}}
                <div class="flex items-start justify-between border-b border-neutral-200 pb-4 dark:border-neutral-800">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold uppercase tracking-wider text-neutral-900 dark:text-neutral-100" x-text="selectedSatker ? selectedSatker.satker : ''"></h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1" x-text="'Rincian Sub Unit - Tahap ' + activeTahap + ' (' + currentData.date_range + ')'"></p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 transition-colors cursor-pointer"
                        x-on:click="closeSubUnitModal()"
                        aria-label="Tutup"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body: Table Sub Unit --}}
                <div class="overflow-y-auto my-4 overflow-x-auto">
                    <table class="w-full border-collapse border border-sky-300 text-xs dark:border-neutral-700">
                        <thead>
                            <tr class="bg-neutral-50/80 text-center font-bold tracking-wider text-neutral-600 dark:bg-neutral-900/60 dark:text-neutral-300">
                                <th rowspan="2" class="border border-sky-300 px-3 py-3 align-middle dark:border-neutral-700 w-12 text-center">
                                    NO
                                </th>
                                <th rowspan="2" class="border border-sky-300 px-4 py-3 align-middle dark:border-neutral-700 text-left">
                                    SUB UNIT
                                </th>
                                <th colspan="4" class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 text-center">
                                    BIDANG
                                </th>
                                <th rowspan="2" class="border border-sky-300 px-4 py-3 align-middle dark:border-neutral-700 w-24 text-center">
                                    JUMLAH
                                </th>
                            </tr>
                            <tr class="bg-neutral-50/80 text-center font-bold tracking-wider text-neutral-600 dark:bg-neutral-900/60 dark:text-neutral-300">
                                <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-20">
                                    OPSNAL
                                </th>
                                <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-20">
                                    SDM
                                </th>
                                <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-20">
                                    LOGISTIK
                                </th>
                                <th class="border border-sky-300 px-4 py-2 align-middle dark:border-neutral-700 w-20">
                                    GARKEU
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-neutral-800 dark:text-neutral-200">
                            <template x-if="selectedSatker && selectedSatker.sub_units">
                                <template x-for="sub in selectedSatker.sub_units" :key="sub.nama_sub">
                                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/40">
                                        <td class="border border-sky-300 px-3 py-3 text-center font-medium dark:border-neutral-700" x-text="sub.no"></td>
                                        <td class="border border-sky-300 px-4 py-3 font-semibold uppercase text-neutral-900 dark:border-neutral-700 dark:text-neutral-100" x-text="sub.nama_sub"></td>
                                        <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="sub.opsnal"></td>
                                        <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="sub.sdm"></td>
                                        <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="sub.logistik"></td>
                                        <td class="border border-sky-300 px-4 py-3 text-center font-medium dark:border-neutral-700" x-text="sub.garkeu"></td>
                                        <td class="border border-sky-300 px-4 py-3 text-center font-bold text-neutral-900 dark:border-neutral-700 dark:text-neutral-100" x-text="sub.jumlah"></td>
                                    </tr>
                                </template>
                            </template>

                            <template x-if="!selectedSatker || !selectedSatker.sub_units || selectedSatker.sub_units.length === 0">
                                <tr>
                                    <td colspan="7" class="border border-sky-300 px-4 py-6 text-center text-neutral-500 italic dark:border-neutral-700">
                                        Tidak ada sub unit terdaftar.
                                    </td>
                                </tr>
                            </template>

                            {{-- Total Highlight Row --}}
                            <template x-if="selectedSatker">
                                <tr class="bg-[#fef3c7] font-bold text-neutral-900 dark:bg-amber-950/40 dark:text-amber-100">
                                    <td colspan="2" class="border border-sky-300 px-4 py-3 text-center font-bold uppercase dark:border-neutral-700">
                                        TOTAL
                                    </td>
                                    <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="selectedSatker.opsnal"></td>
                                    <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="selectedSatker.sdm"></td>
                                    <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="selectedSatker.logistik"></td>
                                    <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="selectedSatker.garkeu"></td>
                                    <td class="border border-sky-300 px-4 py-3 text-center font-bold dark:border-neutral-700" x-text="selectedSatker.jumlah"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end border-t border-neutral-200 pt-3 dark:border-neutral-800">
                    <button
                        type="button"
                        class="rounded-lg bg-neutral-200 px-4 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-300 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer"
                        x-on:click="closeSubUnitModal()"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
        </div>
    </div>
</x-admin-layout>
