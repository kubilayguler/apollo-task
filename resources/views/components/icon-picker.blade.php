{{--
    Reusable icon picker grid.
    Props:
      $name    = form field name (default: 'icon')
      $current = currently selected icon (default: 'folder')
      $icons   = array of icon names
      $id      = unique id for this picker instance (default: 'icon-picker-default')
--}}
@props([
    'name' => 'icon',
    'current' => 'folder',
    'icons' => [],
    'id' => 'icon-picker-default',
])

<div id="{{ $id }}">
    <input type="hidden" name="{{ $name }}" value="{{ $current }}">

    <div
        class="grid grid-cols-7 sm:grid-cols-9 gap-1 max-h-30 overflow-y-auto
                rounded-lg border border-border-dark bg-background-dark p-2">
        @foreach ($icons as $icon)
            <button type="button" onclick="selectIcon('{{ $icon }}', '{{ $id }}')"
                data-icon="{{ $icon }}" title="{{ str_replace('_', ' ', $icon) }}"
                class="icon-opt flex items-center justify-center p-2 {{ $icon === $current ? 'chosen' : '' }}">
                <span class="material-symbols-outlined text-[20px] text-app-muted">{{ $icon }}</span>
            </button>
        @endforeach
    </div>

    {{-- Preview strip --}}
    <div class="mt-2 flex items-center gap-2">
        <span class="text-xs text-app-muted">Selected:</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary shrink-0">
            <span class="icon-preview-sym material-symbols-outlined text-[17px] text-white">{{ $current }}</span>
        </span>
        <span class="icon-preview-lbl text-xs text-app-muted">{{ str_replace('_', ' ', $current) }}</span>
    </div>
</div>
