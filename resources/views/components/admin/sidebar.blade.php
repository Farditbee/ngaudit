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
            'label' => 'Data Master',
            'items' => [
                ['label' => 'User', 'icon' => 'user'],
                ['label' => 'Wilayah', 'icon' => 'map', 'href' => route('master.wilayah.index'), 'active' => request()->routeIs('master.wilayah.*')],
                ['label' => 'Plotting', 'icon' => 'land-plot'],
                ['label' => 'Users', 'icon' => 'users', 'children' => ['List', 'View'], 'open' => true],
                ['label' => 'Chat', 'icon' => 'message-circle', 'pro' => true],
                ['label' => 'Kanban', 'icon' => 'kanban', 'pro' => true],
                ['label' => 'Contact', 'icon' => 'contact', 'pro' => true],
                ['label' => 'Roles & Permissions', 'icon' => 'shield-check', 'pro' => true, 'children' => ['Roles', 'Permissions']],
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

    <nav class="flex-1 space-y-4 overflow-y-auto overflow-x-hidden p-2 group-[.rail]:lg:[scrollbar-width:none] group-[.rail]:lg:[&::-webkit-scrollbar]:hidden">
        <div class="px-1 pt-4 pb-1 group-[.rail]:lg:px-0 group-[.rail]:lg:pt-2">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-md p-1 group-[.rail]:lg:p-0">
            <span class="flex h-12 w-12 shrink-0 group-[.rail]:lg:h-8 group-[.rail]:lg:w-8 items-center justify-center overflow-hidden rounded-lg border border-neutral-200 bg-neutral-100 dark:border-neutral-800 dark:bg-neutral-800">
                <svg viewBox="0 0 24 24" class="h-full w-full fill-neutral-400 dark:fill-neutral-500" aria-hidden="true">
                    <circle cx="12" cy="9" r="4" />
                    <path d="M4 22c0-4.4 3.6-8 8-8s8 3.6 8 8z" />
                </svg>
            </span>
            <span class="flex min-w-0 flex-col group-[.rail]:lg:hidden">
                <span class="truncate text-sm font-bold leading-tight">{{ auth()->user()->name }}</span>
                <span class="truncate text-sm leading-tight">{{ auth()->user()->email }}</span>
            </span>
        </a>
        </div>

        @foreach ($groups as $group)
            <div>
                <p class="flex h-8 items-center px-2 group-[.rail]:lg:hidden text-xs font-medium uppercase text-neutral-500 dark:text-neutral-400">{{ $group['label'] }}</p>
                <ul class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <li>
                            @if (! empty($item['children']))
                                <div x-data="{ open: {{ ! empty($item['open']) ? 'true' : 'false' }} }">
                                    <button type="button" x-on:click="open = !open" class="{{ $itemClass }}">
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
