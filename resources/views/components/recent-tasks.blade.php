@props(['tasks'])

<div class="rounded-xl border border-border-dark bg-surface-dark/40 p-4">
    <h3 class="mb-4 text-sm font-bold text-app-text flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px] text-primary">history</span>
        Recent Tasks
    </h3>

    <div class="space-y-2">
        @forelse ($tasks as $task)
            @php
                $isCompleted = $task->status === 'completed';
                $priority = strtolower($task->priority ?? 'none');
                $priorityClass = match ($priority) {
                    'high' => 'bg-red-500/20 text-red-500',
                    'medium' => 'bg-yellow-600/20 text-yellow-500',
                    'low' => 'bg-blue-600/20 text-blue-500',
                    default => 'bg-slate-500/20 text-slate-500',
                };
            @endphp
            <a href="{{ route('dashboard', ['project' => $task->project_id]) }}"
                class="flex items-center gap-3 rounded-lg border border-border-dark bg-background-dark/50 px-3 py-2 hover:bg-surface-dark transition-colors {{ $isCompleted ? 'opacity-60' : '' }}">

                <div
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border {{ $isCompleted ? 'border-blue-500 bg-blue-600 text-white' : 'border-border-dark bg-transparent' }}">
                    @if ($isCompleted)
                        <span class="material-symbols-outlined text-[12px] font-bold">check</span>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <p
                        class="truncate text-xs font-medium {{ $isCompleted ? 'line-through text-app-muted' : 'text-white' }}">
                        {{ $task->title }}
                    </p>
                    <p class="truncate text-[10px] text-app-muted mt-0.5 flex items-center gap-1">
                        <span
                            class="material-symbols-outlined text-[12px]">{{ $task->project->icon ?? 'folder' }}</span>
                        {{ $task->project->name }}
                    </p>
                </div>

                @if ($priority !== 'none')
                    <span
                        class="shrink-0 rounded-sm px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide {{ $priorityClass }}">
                        {{ $priority }}
                    </span>
                @endif
            </a>
        @empty
            <div
                class="rounded-lg border border-dashed border-border-dark px-4 py-6 text-center text-xs text-app-muted">
                No recent tasks found.
            </div>
        @endforelse
    </div>
</div>
