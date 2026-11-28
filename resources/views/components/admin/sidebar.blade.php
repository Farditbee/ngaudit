@php
    $groups = [
        [
            'label' => 'Dashboard & Layouts',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'gauge', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Layouts', 'icon' => 'layout', 'pro' => true, 'children' => ['Full Navbar', 'Horizontal', 'Split', 'Icon Menu', 'Paper']],
            ],
        ],

        [
            'label' => 'Audit',
            'items' => [
                ['label' => 'Persiapan', 'icon' => 'square-kanban-plus', 'open' => request()->routeIs('audit.sprin.*', 'audit.pka.*'), 'children' => [
                    ['label' => 'Sprin', 'href' => route('audit.sprin.index'), 'active' => request()->routeIs('audit.sprin.*')],
                    'Entry Meeting',
                    ['label' => 'PKA', 'href' => route('audit.pka.index'), 'active' => request()->routeIs('audit.pka.*')],
                    'Dokumen Pendahuluan',
                ]],
                ['label' => 'Pelaksanaan', 'icon' => 'panel-top-open', 'open' => request()->routeIs('audit.kka.*', 'audit.tabulasi.*'), 'children' => [
                    ['label' => 'KKA', 'href' => route('audit.kka.index'), 'active' => request()->routeIs('audit.kka.*') && ! request()->routeIs('audit.kka.tabulasi.*')],
                    ['label' => 'Tabulasi', 'href' => route('audit.tabulasi.index'), 'active' => request()->routeIs('audit.tabulasi.*', 'audit.kka.tabulasi.*')],
                ]],
                ['label' => 'Auditee', 'icon' => 'book-user', 'open' => request()->routeIs('audit.tindak-lanjut.*'), 'children' => [
                    ['label' => 'Tindak Lanjut Auditee', 'href' => route('audit.tindak-lanjut.index'), 'active' => request()->routeIs('audit.tindak-lanjut.*')],
                    'Dummy',
                ]],
            ],
        ],

        [
            'label' => 'Data Master',
            'items' => [
                ['label' => 'User', 'icon' => 'user', 'href' => route('master.user.index'), 'active' => request()->routeIs('master.user.*')],
                ['label' => 'Tim', 'icon' => 'user-group', 'href' => route('master.tim.index'), 'active' => request()->routeIs('master.tim.*')],
                ['label' => 'Perangkat Daerah', 'icon' => 'building-2', 'href' => route('master.wilayah.index'), 'active' => request()->routeIs('master.wilayah.*')],
                ['label' => 'Template', 'icon' => 'notepad-text-dashed', 'open' => request()->routeIs('master.template.*'), 'children' => [
                    'Taklimat',
                    ['label' => 'PKA', 'href' => route('master.template.pka.index'), 'active' => request()->routeIs('master.template.pka.*')],
                    ['label' => 'KKA', 'href' => route('master.template.kka.index'), 'active' => request()->routeIs('master.template.kka.*')],
                    ['label' => 'Langkah Kerja', 'href' => route('master.template.langkah-kerja.index'), 'active' => request()->routeIs('master.template.langkah-kerja.*')],
                ]],
                ['label' => 'Kode', 'icon' => 'users', 'open' => request()->routeIs('master.kode.*', 'master.sub-kode.*', 'master.kriteria.*'), 'children' => [
                    ['label' => 'Kode Temuan', 'href' => route('master.kode.index', 'temuan'), 'active' => request()->is('master/kode/temuan')],
                    ['label' => 'Sub Kode Temuan', 'href' => route('master.sub-kode.index', 'temuan'), 'active' => request()->is('master/sub-kode/temuan')],
                    ['label' => 'Kode Sebab', 'href' => route('master.kode.index', 'sebab'), 'active' => request()->is('master/kode/sebab')],
                    ['label' => 'Sub Kode Sebab', 'href' => route('master.sub-kode.index', 'sebab'), 'active' => request()->is('master/sub-kode/sebab')],
                    ['label' => 'Kode Akibat', 'href' => route('master.kode.index', 'akibat'), 'active' => request()->is('master/kode/akibat')],
                    ['label' => 'Sub Kode Akibat', 'href' => route('master.sub-kode.index', 'akibat'), 'active' => request()->is('master/sub-kode/akibat')],
                    ['label' => 'Kode Rekomendasi', 'href' => route('master.kode.index', 'rekomendasi'), 'active' => request()->is('master/kode/rekomendasi')],
                    ['label' => 'Sub Kode Rekomendasi', 'href' => route('master.sub-kode.index', 'rekomendasi'), 'active' => request()->is('master/sub-kode/rekomendasi')],
                    ['label' => 'Kode Tinjut', 'href' => route('master.kode.index', 'tinjut'), 'active' => request()->is('master/kode/tinjut')],
                    ['label' => 'Sub Kode Tinjut', 'href' => route('master.sub-kode.index', 'tinjut'), 'active' => request()->is('master/sub-kode/tinjut')],
                    ['label' => 'Kriteria', 'href' => route('master.kriteria.index'), 'active' => request()->routeIs('master.kriteria.*')],
                ]],
            ],
        ],
        [
            'label' => 'Apps',
            'items' => [
                ['label' => 'Mail', 'icon' => 'mail'],
                ['label' => 'Calendar', 'icon' => 'calendar'],
                ['label' => 'Users', 'icon' => 'users', 'children' => ['List', 'View'], 'open' => true],
                ['label' => 'Chat', 'icon' => 'message-circle', 'pro' => true],
                ['label' => 'Kanban', 'icon' => 'kanban', 'pro' => true],
                ['label' => 'Contact', 'icon' => 'contact', 'pro' => true],
                ['label' => 'Roles & Permissions', 'icon' => 'shield-check', 'pro' => true, 'children' => ['Roles', 'Permissions']],
            ],
        ],
        [
            'label' => 'Pages',
            'items' => [
                ['label' => 'User Settings', 'icon' => 'user-cog', 'children' => [
                    'General', 'Workspace',
                    ['label' => 'Notifications', 'pro' => true], ['label' => 'Integrations', 'pro' => true],
                    ['label' => 'Members', 'pro' => true], ['label' => 'Security', 'pro' => true],
                    ['label' => 'Billing & Usage', 'pro' => true],
                ]],
                ['label' => 'User Profile', 'icon' => 'user', 'children' => [
                    ['label' => 'Profile', 'href' => route('profile.edit')], 'Connections',
                    ['label' => 'Teams', 'pro' => true], ['label' => 'Projects', 'pro' => true],
                ]],
                ['label' => 'Authentication', 'icon' => 'lock-keyhole', 'children' => array_map(
                    fn (string $name): array => ['label' => $name, 'children' => [
                        $name.' v1', ['label' => $name.' v2', 'pro' => true], ['label' => $name.' v3', 'pro' => true],
                    ]],
                    ['Login', 'Register', 'Forgot Password', 'Verify Email', 'Reset Password', 'Two Steps'],
                )],
                ['label' => 'Error Pages', 'icon' => 'bug', 'children' => [
                    'Error Page',
                    ['label' => 'Error Page - 404', 'pro' => true], ['label' => 'Not Authorized - 401', 'pro' => true],
                    ['label' => 'Forbidden - 403', 'pro' => true], ['label' => 'Server Error - 500', 'pro' => true],
                    ['label' => 'Under Maintenance', 'pro' => true],
                ]],
                ['label' => 'Landing Page', 'icon' => 'rocket', 'pro' => true],
                ['label' => 'Pricing', 'icon' => 'dollar-sign', 'pro' => true],
                ['label' => 'FAQ', 'icon' => 'circle-help', 'pro' => true],
                ['label' => 'Onboarding', 'icon' => 'footprints', 'pro' => true, 'children' => ['Onboarding v1', 'Onboarding v2']],
                ['label' => 'Empty State', 'icon' => 'file', 'pro' => true, 'children' => ['Empty State v1', 'Empty State v2']],
            ],
        ],
        [
            'label' => 'Forms & Tables',
            'items' => [
                ['label' => 'Form Layouts', 'icon' => 'layout-template', 'children' => [
                    'Vertical Layout', 'Horizontal Layout', ['label' => 'Sticky Actions', 'pro' => true],
                ]],
                ['label' => 'Form Validation', 'icon' => 'badge-check'],
                ['label' => 'Data Table', 'icon' => 'table'],
                ['label' => 'Form Wizard', 'icon' => 'list-todo', 'pro' => true, 'children' => ['Icons', 'Numbered']],
            ],
        ],
        [
            'label' => 'Components & Charts',
            'items' => [
                ['label' => 'Components', 'icon' => 'layout-grid'],
                ['label' => 'Charts', 'icon' => 'line-chart'],
                ['label' => 'Statistics', 'icon' => 'chart-column'],
                ['label' => 'Card Nav', 'icon' => 'panel-top', 'pro' => true],
                ['label' => 'Widgets', 'icon' => 'puzzle'],
            ],
        ],
        [
            'label' => 'Miscellaneous',
            'items' => [
                ['label' => 'Menu Level', 'icon' => 'menu', 'children' => [
                    'Menu Item', ['label' => 'Menu Level 1', 'children' => ['Menu Level 2']],
                ]],
                ['label' => 'Support', 'icon' => 'info'],
                ['label' => 'Documentation', 'icon' => 'book-open-text'],
            ],
        ],
    ];
    $itemClass = 'flex h-8 w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800';

    $authUser = auth()->user();
    $sidebarRegions = [];

    if ($authUser) {
        if ($authUser->struktur_perangkat_daerah_id) {
            $spd = \Illuminate\Support\Facades\DB::table('struktur_perangkat_daerah as spd')
                ->leftJoin('struktur_perangkat_daerah as parent', 'parent.id', '=', 'spd.parent_id')
                ->where('spd.id', $authUser->struktur_perangkat_daerah_id)
                ->select([
                    'spd.*',
                    'parent.nama_unit as parent_nama_unit',
                    'parent.level as parent_level',
                    'parent.jenis_unit as parent_jenis_unit',
                ])
                ->first();

            if ($spd) {
                $hasParent = false;
                $parentName = null;

                if (! empty($spd->parent_nama_unit)) {
                    $level = isset($spd->level) ? (int) $spd->level : null;
                    if ($level !== null && $level > 1) {
                        $hasParent = true;
                        $parentName = (string) $spd->parent_nama_unit;
                    } elseif (! in_array($spd->jenis_unit, ['Kecamatan', 'Dinas', 'Badan', 'Inspektorat', 'Sekretariat', 'Sekretariat Daerah', 'Sekretariat DPRD'], true)) {
                        $hasParent = true;
                        $parentName = (string) $spd->parent_nama_unit;
                    }
                }

                $isKecamatan = $authUser->level === 'Kecamatan' || $spd->jenis_unit === 'Kecamatan' || str_contains(strtolower($spd->nama_unit), 'kecamatan') || str_contains(strtolower((string) $parentName), 'kecamatan');

                $badgeClass = $isKecamatan
                    ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                    : 'border border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-800/60 dark:bg-indigo-950/40 dark:text-indigo-300';

                if ($hasParent && $parentName) {
                    $sidebarRegions[] = [
                        'label' => $parentName,
                        'class' => $badgeClass,
                    ];

                    $subName = preg_replace('/\s*-\s*Kec\.?\s+.*$/i', '', $spd->nama_unit);
                    $sidebarRegions[] = [
                        'label' => $subName,
                        'class' => 'border border-purple-200 bg-purple-50/80 text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300',
                    ];
                } else {
                    $sidebarRegions[] = [
                        'label' => $spd->nama_unit,
                        'class' => $badgeClass,
                    ];
                }
            }
        } elseif ($authUser->level) {
            $isKecamatan = $authUser->level === 'Kecamatan';
            $badgeClass = $isKecamatan
                ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                : 'border border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-800/60 dark:bg-indigo-950/40 dark:text-indigo-300';

            $sidebarRegions[] = [
                'label' => $authUser->level,
                'class' => $badgeClass,
            ];
        } elseif ($authUser->kode_wilayah) {
            $cleanCode = str_replace('.', '', (string) $authUser->kode_wilayah);
            $wilayahRow = \Illuminate\Support\Facades\DB::table('struktur_perangkat_daerah')
                ->where('kode_wilayah', $cleanCode)
                ->orWhere('id', (int) $authUser->kode_wilayah)
                ->first();
            if ($wilayahRow) {
                if (! empty($wilayahRow->nama_unit)) {
                    $sidebarRegions[] = [
                        'label' => $wilayahRow->nama_unit,
                        'class' => 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300',
                    ];
                }
                if (! empty($wilayahRow->desa_kelurahan)) {
                    $sidebarRegions[] = [
                        'label' => $wilayahRow->desa_kelurahan,
                        'class' => 'border border-red-200 bg-red-50 text-red-700 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-300',
                    ];
                }
            }
        }
    }
@endphp

<aside
    class="group fixed inset-y-0 left-0 z-40 flex w-64 flex-col overflow-hidden border-r border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 transition-[transform,width] duration-200"
    x-bind:class="{
        '-translate-x-full': !mobileOpen,
        'translate-x-0': mobileOpen,
        'lg:translate-x-0': true,
        'rail lg:w-12': collapsed,
    }">
    <div class="flex h-[4.3125rem] shrink-0 items-center px-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-md px-2 group-[.rail]:lg:px-0">
            <x-application-logo class="h-8 w-8 shrink-0" />
            <div class="flex flex-col items-start group-[.rail]:lg:hidden">
                <span class="text-lg font-semibold leading-tight">{{ config('app.name') }}</span>
                <span class="text-xs font-light">Dashboard Template</span>
            </div>
        </a>
    </div>

    <nav class="flex-1 space-y-4 overflow-y-auto overflow-x-hidden p-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="px-1 pt-4 pb-1 group-[.rail]:lg:px-0 group-[.rail]:lg:pt-2">
        <div class="flex items-start gap-3 rounded-md p-1 group-[.rail]:lg:p-0">
            <span class="mt-0.5 flex h-12 w-12 shrink-0 group-[.rail]:lg:h-8 group-[.rail]:lg:w-8 items-center justify-center overflow-hidden rounded-lg border border-neutral-200 bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-800">
                <svg viewBox="0 0 24 24" class="h-full w-full fill-neutral-400 dark:fill-neutral-500" aria-hidden="true">
                    <circle cx="12" cy="9" r="4" />
                    <path d="M4 22c0-4.4 3.6-8 8-8s8 3.6 8 8z" />
                </svg>
            </span>
            <span class="flex min-w-0 flex-col gap-0.5 group-[.rail]:lg:hidden">
                <span class="break-words text-xs font-bold uppercase leading-snug text-neutral-900 dark:text-neutral-100">{{ $authUser?->name }}</span>
                <span class="truncate text-xs leading-snug text-neutral-600 dark:text-neutral-400">{{ $authUser?->email }}</span>
                @if (! empty($authUser?->role))
                    <span class="text-xs font-medium leading-snug text-neutral-800 dark:text-neutral-200">{{ $authUser->role }}</span>
                @endif

                @if (! empty($sidebarRegions))
                    <span class="mt-0.5 flex flex-col items-start gap-1 text-xs text-neutral-700 dark:text-neutral-300">
                        @foreach ($sidebarRegions as $region)
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-medium leading-tight {{ $region['class'] }}">
                                {{ $region['label'] }}
                            </span>
                        @endforeach
                    </span>
                @endif

                @if (! empty($authUser?->bidang))
                    <span class="mt-0.5 text-xs leading-snug text-neutral-700 dark:text-neutral-300">
                        Bidang: {{ $authUser->bidang }}
                    </span>
                @endif
            </span>
        </div>
        </div>

        @foreach ($groups as $group)
            <div>
                <p class="flex h-8 items-center px-2 group-[.rail]:lg:hidden text-xs font-medium uppercase text-neutral-500 dark:text-neutral-400">{{ $group['label'] }}</p>
                <ul class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <li>
                            @if (! empty($item['children']))
                                @php
                                    $hasActiveChild = collect($item['children'])->contains(fn ($c) => is_array($c) && ! empty($c['active']));
                                    $isOpen = ! empty($item['open']) || $hasActiveChild;
                                @endphp
                                <div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }">
                                    <button type="button" x-on:click="open = !open" class="{{ $itemClass }} {{ $hasActiveChild ? 'bg-neutral-100/70 font-medium text-neutral-900 dark:bg-neutral-800/70 dark:text-neutral-100' : '' }}">
                                        <x-dynamic-component :component="'lucide-'.$item['icon']"  class="h-4 w-4 shrink-0" />
                                        <span class="flex-1 truncate text-left group-[.rail]:lg:hidden">{{ $item['label'] }}</span>
                                        @if (! empty($item['pro']))
                                            <span class="rounded-md bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 text-xs font-medium group-[.rail]:lg:hidden">Pro</span>
                                        @endif
                                        <x-lucide-chevron-right class="h-4 w-4 transition-transform group-[.rail]:lg:hidden" x-bind:class="open && 'rotate-90'" />
                                    </button>
                                    <ul x-show="open" x-cloak class="group-[.rail]:lg:hidden ms-4 mt-1 space-y-1 border-s border-neutral-200 dark:border-neutral-800 ps-2.5">
                                        @include('components.admin.nav-sub', ['items' => $item['children']])
                                    </ul>
                                </div>
                            @else
                                <a href="{{ $item['href'] ?? '#' }}"
                                    class="{{ $itemClass }} {{ ! empty($item['active']) ? 'bg-neutral-100 dark:bg-neutral-800 font-medium' : '' }}">
                                    <x-dynamic-component :component="'lucide-'.$item['icon']"  class="h-4 w-4 shrink-0" />
                                    <span class="flex-1 truncate group-[.rail]:lg:hidden">{{ $item['label'] }}</span>
                                    @if (! empty($item['pro']))
                                        <span class="rounded-md bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 text-xs font-medium group-[.rail]:lg:hidden">Pro</span>
                                    @endif
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>
