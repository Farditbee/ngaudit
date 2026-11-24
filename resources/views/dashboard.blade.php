@php
    $statistics = [
        ['icon' => 'truck', 'value' => '42', 'title' => 'Shipped Orders', 'change' => '+18.2%'],
        ['icon' => 'triangle-alert', 'value' => '8', 'title' => 'Damaged Returns', 'change' => '-8.7%'],
        ['icon' => 'calendar-x', 'value' => '27', 'title' => 'Missed Delivery Slots', 'change' => '+4.3%'],
    ];

    $earnings = [
        ['img' => 'zipcar.webp', 'platform' => 'Zipcar', 'technologies' => 'Vuejs & HTML', 'earnings' => '-$23,569.26', 'progress' => 75],
        ['img' => 'bitbank.webp', 'platform' => 'Bitbank', 'technologies' => 'Figma & React', 'earnings' => '-$12,650.31', 'progress' => 25],
    ];

    $metrics = [
        ['icon' => 'trending-up', 'title' => 'Sales trend', 'value' => '$11,548'],
        ['icon' => 'badge-percent', 'title' => 'Discount offers', 'value' => '$1,326'],
        ['icon' => 'dollar-sign', 'title' => 'Net profit', 'value' => '$17,356'],
        ['icon' => 'shopping-bag', 'title' => 'Total orders', 'value' => '248'],
    ];

    $reachBars = [168, 305, 213, 330, 305];

    $salesPlan = 54;
    $totalBars = 24;
    $filledBars = (int) round(($salesPlan * $totalBars) / 100);

    $radius = 45;
    $circumference = 2 * M_PI * $radius;
    $donutSegments = [
        ['value' => 340, 'color' => 'stroke-neutral-900 dark:stroke-neutral-100'],
        ['value' => 200, 'color' => 'stroke-neutral-500'],
        ['value' => 200, 'color' => 'stroke-neutral-300 dark:stroke-neutral-700'],
    ];
    $donutTotal = array_sum(array_column($donutSegments, 'value'));

    $transactions = [
        ['Jack Alfredo', 'jack@shadcnstudio.com', 316.00, 'paid', 'Mastercard'],
        ['Maria Gonzalez', 'maria.g@shadcnstudio.com', 253.40, 'pending', 'Visa'],
        ['John Doe', 'john.doe@shadcnstudio.com', 852.00, 'paid', 'Mastercard'],
        ['Emily Carter', 'emily.carter@shadcnstudio.com', 889.00, 'pending', 'Visa'],
        ['David Lee', 'david.lee@shadcnstudio.com', 723.16, 'paid', 'Mastercard'],
        ['Sophia Patel', 'sophia.patel@shadcnstudio.com', 612.00, 'failed', 'Mastercard'],
        ['Robert Wilson', 'robert.wilson@shadcnstudio.com', 445.25, 'paid', 'Visa'],
        ['Lisa Martinez', 'lisa.martinez@shadcnstudio.com', 297.80, 'processing', 'Mastercard'],
        ['Michael Thompson', 'michael.thompson@shadcnstudio.com', 756.90, 'paid', 'Visa'],
        ['Amanda Johnson', 'amanda.johnson@shadcnstudio.com', 189.50, 'pending', 'Mastercard'],
        ['Kevin Brown', 'kevin.brown@shadcnstudio.com', 1024.75, 'paid', 'Visa'],
        ['Sarah Davis', 'sarah.davis@shadcnstudio.com', 367.20, 'failed', 'Mastercard'],
        ['Christopher Garcia', 'christopher.garcia@shadcnstudio.com', 598.45, 'processing', 'Visa'],
        ['Jennifer Rodriguez', 'jennifer.rodriguez@shadcnstudio.com', 821.30, 'paid', 'Mastercard'],
    ];
    $transactions = collect($transactions)->map(fn (array $row, int $index): array => [
        'avatar' => asset('images/avatars/avatar-'.($index + 1).'.webp'),
        'name' => $row[0],
        'email' => $row[1],
        'amount' => number_format($row[2], 2),
        'status' => $row[3],
        'paidBy' => $row[4],
    ])->values();

    $statusClasses = [
        'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    ];
    $card = 'rounded-xl bg-white dark:bg-neutral-950 ring-1 ring-neutral-900/10 dark:ring-white/10 shadow-sm';
    $innerCard = 'rounded-xl bg-white dark:bg-neutral-950 ring-1 ring-neutral-900/10 dark:ring-white/10';
@endphp

<x-admin-layout :breadcrumbs="['Dashboard', 'Orders']">
    <div class="grid grid-cols-2 gap-6 lg:grid-cols-3">
        {{-- Statistics cards --}}
        <div class="col-span-full grid gap-6 sm:grid-cols-3 md:max-lg:grid-cols-1">
            @foreach ($statistics as $stat)
                <div class="{{ $card }} flex flex-col gap-4 p-6">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-sm bg-neutral-900/10 dark:bg-white/10 text-neutral-900 dark:text-neutral-100">
                            <x-dynamic-component :component="'lucide-'.$stat['icon']"  class="h-4 w-4" />
                        </div>
                        <span class="text-2xl">{{ $stat['value'] }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span class="text-base font-semibold">{{ $stat['title'] }}</span>
                        <p class="space-x-2 text-sm"><span>{{ $stat['change'] }}</span><span class="text-neutral-500 dark:text-neutral-400">than last week</span></p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 max-xl:col-span-full lg:max-xl:grid-cols-2">
            {{-- Product insight --}}
            <div class="{{ $card }} flex flex-col justify-between gap-5 p-6">
                <div class="flex justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-lg font-semibold">Product insight</span>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Published on 12 MAY 2025 - 6:10 PM</span>
                    </div>
                    <img src="{{ asset('images/widgets/image-7.webp') }}" alt="Product" class="h-20 w-[5.125rem] rounded-md object-cover">
                </div>
                <div class="h-px bg-neutral-200"></div>
                <div class="space-y-4">
                    @foreach ([['Product reached', '21,153', 'fill-neutral-900 dark:fill-neutral-100'], ['Order placed', '2,123', 'fill-neutral-900/10 dark:fill-white/10']] as [$label, $value, $fill])
                        <div class="flex items-center justify-between gap-1">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs">{{ $label }}</span>
                                <span class="text-2xl font-semibold">{{ $value }}</span>
                            </div>
                            <svg viewBox="0 0 72 52" class="h-[3.25rem] w-[4.5rem]" aria-hidden="true">
                                @foreach ($reachBars as $i => $bar)
                                    @php $h = round(($bar / 330) * 52, 1); @endphp
                                    <rect x="{{ $i * 16 }}" y="{{ 52 - $h }}" width="8" height="{{ $h }}" rx="2" class="{{ $fill }}" />
                                @endforeach
                            </svg>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Total earning --}}
            <div class="{{ $card }} flex flex-col justify-between gap-5 p-6 sm:min-w-0">
                <div class="flex flex-col gap-6">
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold">Total Earning</div>
                        <x-lucide-ellipsis-vertical class="h-4 w-4 text-neutral-500 dark:text-neutral-400" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-semibold">$24650</span>
                            <span class="flex items-center gap-1"><x-lucide-chevron-up class="h-4 w-4" /><span class="text-sm">10%</span></span>
                        </div>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Compare to last year ($84,325)</span>
                    </div>
                </div>
                <div class="flex flex-1 flex-col justify-evenly gap-4">
                    @foreach ($earnings as $earning)
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-sm bg-neutral-900/10 dark:bg-white/10">
                                    <img src="{{ asset('images/widgets/'.$earning['img']) }}" alt="{{ $earning['platform'] }}" class="h-6 w-6">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-base font-medium">{{ $earning['platform'] }}</span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $earning['technologies'] }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm">{{ $earning['earnings'] }}</p>
                                <div class="h-1.5 w-36 overflow-hidden rounded-full bg-neutral-900/20 dark:bg-white/20">
                                    <div class="h-full rounded-full bg-neutral-900 dark:bg-neutral-100" style="width: {{ $earning['progress'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sales metrics --}}
        <div class="{{ $card }} col-span-full space-y-6 p-6 xl:col-span-2">
            <div class="grid gap-6 lg:grid-cols-5">
                <div class="flex flex-col justify-between gap-7 lg:col-span-3">
                    <span class="text-lg font-semibold">Sales metrics</span>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/brands/logo-square.webp') }}" class="h-[2.625rem] w-[2.625rem] rounded-lg" alt="logo">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xl font-medium">Sandy' Company</span>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">sandy@company.com</span>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($metrics as $metric)
                            <div class="{{ $innerCard }} flex items-center gap-3 px-4 py-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-neutral-900/10 dark:bg-white/10">
                                    <x-dynamic-component :component="'lucide-'.$metric['icon']"  class="h-5 w-5" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $metric['title'] }}</span>
                                    <span class="text-lg font-medium">{{ $metric['value'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="{{ $innerCard }} flex flex-col gap-4 p-6 lg:col-span-2">
                    <span class="text-lg font-semibold">Revenue goal</span>
                    <div class="flex justify-center">
                        <svg viewBox="0 0 120 120" class="h-[9.625rem] w-[9.625rem]" role="img" aria-label="Revenue goal">
                            @php $offset = 0; @endphp
                            @foreach ($donutSegments as $segment)
                                @php
                                    $length = $circumference * $segment['value'] / $donutTotal;
                                @endphp
                                <circle cx="60" cy="60" r="{{ $radius }}" fill="none" class="{{ $segment['color'] }}" stroke-width="20"
                                    stroke-dasharray="{{ round($length - 1, 2) }} {{ round($circumference - $length + 1, 2) }}"
                                    stroke-dashoffset="{{ round(-$offset, 2) }}" transform="rotate(-90 60 60)" />
                                @php $offset += $length; @endphp
                            @endforeach
                            <text x="60" y="60" text-anchor="middle" class="fill-neutral-900 dark:fill-neutral-100 text-[14px] font-bold">256.24</text>
                            <text x="60" y="76" text-anchor="middle" class="fill-neutral-500 dark:fill-neutral-400 text-[8px]">Total Profit</text>
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xl">Plan completed</span>
                        <span class="text-2xl font-medium">56%</span>
                    </div>
                </div>
            </div>

            <div class="{{ $innerCard }} grid gap-4 p-6 lg:grid-cols-5">
                <div class="flex flex-col justify-center gap-6">
                    <span class="text-lg font-semibold">Sales plan</span>
                    <span class="text-5xl lg:text-6xl">{{ $salesPlan }}%</span>
                    <span class="text-sm text-neutral-500 dark:text-neutral-400">Percentage profit from total sales</span>
                </div>
                <div class="flex flex-col gap-6 text-lg md:col-span-4">
                    <span class="font-medium">Cohort analysis indicators</span>
                    <span class="text-neutral-500 dark:text-neutral-400">Analyzes the behaviour of a group of users who joined a product/service at the same time. over a certain period.</span>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="flex items-center gap-2"><x-lucide-chart-no-axes-combined class="h-6 w-6" /><span class="text-lg font-medium">Open Statistics</span></div>
                        <div class="flex items-center gap-2"><x-lucide-circle-percent class="h-6 w-6" /><span class="text-lg font-medium">Percentage Change</span></div>
                    </div>
                    <div class="flex h-8 w-full items-center gap-1">
                        @for ($i = 0; $i < $totalBars; $i++)
                            <div class="h-full max-w-4 flex-1 rounded-full {{ $i < $filledBars ? 'bg-neutral-900 dark:bg-neutral-100' : 'bg-neutral-900/10 dark:bg-white/10' }}"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="{{ $card }} col-span-full w-full overflow-hidden"
            x-data="{ page: 1, perPage: 7, total: {{ $transactions->count() }}, get pages() { return Math.ceil(this.total / this.perPage) }, visible(i) { return i >= (this.page - 1) * this.perPage && i < this.page * this.perPage } }">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 text-neutral-500 dark:text-neutral-400">
                            <th class="px-6 py-3 font-medium">Customer</th>
                            <th class="px-6 py-3 font-medium">Amount</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Paid by</th>
                            <th class="px-6 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $index => $row)
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 last:border-0" x-show="visible({{ $index }})" @if ($index >= 7) x-cloak @endif>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $row['avatar'] }}" alt="{{ $row['name'] }}" class="h-9 w-9 rounded-full object-cover">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ $row['name'] }}</span>
                                            <span class="text-neutral-500 dark:text-neutral-400">{{ $row['email'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">${{ $row['amount'] }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $statusClasses[$row['status']] }}">{{ $row['status'] }}</span>
                                </td>
                                <td class="px-6 py-3">{{ $row['paidBy'] }}</td>
                                <td class="px-6 py-3">
                                    <button type="button" class="rounded-md p-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800" aria-label="Edit item"><x-lucide-ellipsis-vertical class="h-4 w-4" /></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-neutral-200 dark:border-neutral-800 px-6 py-4 text-sm">
                <p class="text-neutral-500 dark:text-neutral-400">
                    Showing <span class="text-neutral-900 dark:text-neutral-100" x-text="(page - 1) * perPage + 1"></span> to
                    <span class="text-neutral-900 dark:text-neutral-100" x-text="Math.min(page * perPage, total)"></span> of
                    <span class="text-neutral-900 dark:text-neutral-100" x-text="total"></span> entries
                </p>
                <div class="flex items-center gap-1">
                    <button type="button" x-on:click="page--" x-bind:disabled="page === 1"
                        class="flex h-8 items-center gap-1 rounded-md px-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 disabled:opacity-50">
                        <x-lucide-chevron-left class="h-4 w-4" /> Previous
                    </button>
                    <template x-for="n in pages" x-bind:key="n">
                        <button type="button" x-on:click="page = n" x-text="n"
                            class="h-8 w-8 rounded-md text-sm"
                            x-bind:class="page === n ? 'border border-neutral-200 dark:border-neutral-800 font-medium' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'"></button>
                    </template>
                    <button type="button" x-on:click="page++" x-bind:disabled="page === pages"
                        class="flex h-8 items-center gap-1 rounded-md px-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 disabled:opacity-50">
                        Next <x-lucide-chevron-right class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
