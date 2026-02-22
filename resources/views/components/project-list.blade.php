@props(['projects'])

<div class="rounded-xl border border-border-dark bg-surface-dark/40 p-4">
    <h3 class="mb-4 text-sm font-bold text-app-text flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px] text-primary">folder_open</span>
        Projects Overview
    </h3>

    <div class="space-y-3">
        @forelse ($projects as $project)
            <a href="{{ route('dashboard', ['project' => $project->id]) }}"
                class="block rounded-lg border border-border-dark bg-background-dark/50 p-3 hover:bg-surface-dark transition-colors">

                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span
                            class="material-symbols-outlined text-[16px] text-app-muted">{{ $project->icon ?? 'folder' }}</span>
                        <p class="truncate text-xs font-semibold text-white">{{ $project->name }}</p>
                    </div>
                    <span
                        class="text-[10px] font-medium text-app-muted shrink-0">{{ $project->completion_percentage }}%</span>
                </div>

                <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-dark">
                    <div class="h-full rounded-full bg-primary transition-all duration-500"
                        style="width: {{ $project->completion_percentage }}%"></div>
                </div>

                <div class="mt-2 flex items-center justify-between text-[10px] text-app-muted">
                    <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks</span>
                    <span>{{ $project->updated_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <div
                class="rounded-lg border border-dashed border-border-dark px-4 py-6 text-center text-xs text-app-muted">
                No projects yet. Create one from the sidebar.
            </div>
        @endforelse
    </div>
</div>
