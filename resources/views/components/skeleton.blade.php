@props([
    'rows' => 4,
    'avatar' => false,
    'lines' => 2,
])

<div class="space-y-3" role="status" aria-label="Loading…">
    @for ($i = 0; $i < $rows; $i++)
        <div class="flex items-start gap-3 rounded-xl bg-surface-dark/40 p-4 animate-pulse">

            @if ($avatar)
                <div class="h-9 w-9 shrink-0 rounded-lg bg-border-dark/50"></div>
            @else
                <div class="mt-1 h-5 w-5 shrink-0 rounded border-2 bg-border-dark/50"></div>
            @endif

            <div class="flex-1 space-y-2 min-w-0">
                <div class="h-3.5 w-3/5 rounded-full bg-border-dark/50"></div>
                @if ($lines >= 2)
                    <div class="h-2.5 w-2/5 rounded-full bg-border-dark/30"></div>
                @endif
                @if ($lines >= 3)
                    <div class="h-2.5 w-1/4 rounded-full bg-border-dark/20"></div>
                @endif
            </div>

            <div class="h-5 w-14 shrink-0 rounded-full bg-border-dark/30"></div>
        </div>
    @endfor
</div>
