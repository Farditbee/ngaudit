@foreach ($items as $child)
    @php
        $child = is_string($child) ? ['label' => $child] : $child;
        $isActive = ! empty($child['active']);
        $subClass = 'flex h-7 w-full min-w-0 items-center gap-2 overflow-hidden rounded-md px-2 text-sm transition-colors hover:bg-neutral-100 dark:hover:bg-neutral-800';
        $activeSubClass = $isActive
            ? 'bg-neutral-100 font-semibold text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100'
            : 'text-neutral-600 dark:text-neutral-400';
    @endphp
    <li>
        @if (! empty($child['children']))
            <div x-data="{ open: {{ ! empty($child['open']) ? 'true' : 'false' }} }">
                <button type="button" x-on:click="open = !open" class="{{ $subClass }} {{ $activeSubClass }}">
                    <span class="flex-1 truncate text-left">{{ $child['label'] }}</span>
                    <x-lucide-chevron-right class="h-3.5 w-3.5 transition-transform" x-bind:class="open && 'rotate-90'" />
                </button>
                <ul x-show="open" x-cloak class="ms-3 mt-1 space-y-1 border-s border-neutral-200 dark:border-neutral-800 ps-2.5">
                    @include('components.admin.nav-sub', ['items' => $child['children']])
                </ul>
            </div>
        @else
            <a href="{{ $child['href'] ?? '#' }}" class="{{ $subClass }} {{ $activeSubClass }}">
                <span class="flex-1 truncate">{{ $child['label'] }}</span>
                @if (! empty($child['pro']))
                    <span class="rounded-md bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 text-xs font-medium">Pro</span>
                @endif
            </a>
        @endif
    </li>
@endforeach
