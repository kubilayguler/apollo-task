@props([
    'type' => 'info',
    'title' => null,
    'dismiss' => true,
])

@php
    $map = [
        'success' => ['border-green-500/30', 'bg-green-500/10', 'text-green-300', 'check_circle'],
        'error' => ['border-red-500/30', 'bg-red-500/10', 'text-red-300', 'error'],
        'warning' => ['border-yellow-500/30', 'bg-yellow-500/10', 'text-yellow-300', 'warning'],
        'info' => ['border-blue-500/30', 'bg-blue-500/10', 'text-blue-300', 'info'],
    ];
    [$border, $bg, $text, $icon] = $map[$type] ?? $map['info'];
@endphp

<div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
    class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] flex items-start gap-3 rounded-xl border {{ $border }} {{ $bg }} px-4 py-3 text-sm {{ $text }} shadow-lg min-w-[300px] max-w-[90vw]"
    role="alert">

    <span class="material-symbols-outlined text-[18px] mt-0.5 shrink-0 opacity-80"
        style="font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24">{{ $icon }}</span>

    <div class="flex-1 min-w-0">
        @if ($title)
            <p class="font-semibold mb-0.5">{{ $title }}</p>
        @endif
        <div class="leading-relaxed">{{ $slot }}</div>
    </div>

    @if ($dismiss)
        <button @click="visible = false" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity -mr-1">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    @endif
</div>
