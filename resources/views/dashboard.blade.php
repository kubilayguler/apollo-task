@extends('layouts.app')

@section('title', isset($selectedProject) ? $selectedProject->name . ' Tasks' : 'Projects')

@section('main')
    <header
        class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3 border-b border-border-dark bg-background-dark/80 px-4 sm:px-6 backdrop-blur-md">
        <button onclick="openSidebar()"
            class="lg:hidden p-1.5 rounded-lg hover:bg-surface-dark text-app-muted transition-colors shrink-0">
            <span class="material-symbols-outlined text-xl">menu</span>
        </button>

        @if (isset($selectedProject))
            <div class="flex items-center gap-2 min-w-0">
                <h2 class="text-sm sm:text-base font-bold truncate">{{ $selectedProject->name }}</h2>
            </div>
            <span
                class="hidden sm:inline-flex items-center rounded-full border border-border-dark bg-surface-dark px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted shrink-0">Active</span>
            <div class="ml-auto flex items-center gap-1 shrink-0">
                <a href="{{ route('projects.settings', $selectedProject->id) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs text-app-muted hover:bg-surface-dark hover:text-app-text transition-colors">
                    <span class="material-symbols-outlined text-[16px]">settings</span>
                    <span class="hidden sm:inline">Settings</span>
                </a>
            </div>
        @else
            <h2 class="text-sm sm:text-base font-bold">Projects</h2>
        @endif
    </header>

    <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
        <div class="mx-auto w-full max-w-4xl">
            @if (session('status'))
                <x-alert type="success">{{ session('status') }}</x-alert>
            @endif

            @if (isset($selectedProject))
                <form action="{{ route('tasks.store', $selectedProject->id) }}" method="POST"
                    class="mb-4 grid grid-cols-1 gap-2 rounded-xl border border-border-dark bg-surface-dark/30 p-3 md:grid-cols-[minmax(0,1fr)_150px_170px_auto]">
                    @csrf
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Add task"
                        class="w-full rounded-lg border border-border-dark bg-background-dark px-3 py-2 text-sm focus:border-primary focus:outline-none">
                    <select name="priority"
                        class="rounded-lg border border-border-dark bg-background-dark px-3 py-2 text-sm">
                        <option value="none" @selected(old('priority') === 'none')>No priority</option>
                        <option value="low" @selected(old('priority') === 'low')>Low priority</option>
                        <option value="medium" @selected(old('priority') === 'medium')>Medium priority</option>
                        <option value="high" @selected(old('priority') === 'high')>High priority</option>
                    </select>
                    <div class="relative">
                        <input type="text" name="due_date" value="{{ old('due_date') }}" data-datepicker
                            placeholder="Due date"
                            class="w-full rounded-lg border border-border-dark bg-background-dark px-3 py-2 pr-9 text-sm focus:border-primary focus:outline-none">
                        <button type="button" onclick="clearDate(this)" data-clear-date
                            class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-app-muted hover:text-app-text">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                    </div>
                    <button type="submit"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Add</button>
                </form>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-3 text-xs text-app-muted">
                    {{ $tasks->count() }} tasks
                </div>

                <div class="space-y-2">
                    @forelse ($tasks as $task)
                        @php
                            $dueAt = $task->due_date;
                            $isCompleted = $task->status === 'completed';
                            $isPending = !$isCompleted;
                            $isOverdue = $dueAt && $isPending && $dueAt->isPast();

                            $priority = strtolower($task->priority ?? 'none');
                            $priorityClass = match ($priority) {
                                'high' => 'bg-red-500/15 text-red-300',
                                'medium' => 'bg-blue-500/15 text-blue-300',
                                'low' => 'bg-slate-500/15 text-slate-300',
                                default => 'bg-slate-500/10 text-slate-400',
                            };

                            $dueMetaText = null;
                            $dueMetaClass = 'text-app-muted';

                            if ($isCompleted && $task->completed_at) {
                                $dueMetaText = 'Completed ' . $task->completed_at->diffForHumans();
                            } elseif ($dueAt && $isPending) {
                                if ($isOverdue) {
                                    $dueMetaText = 'Overdue';
                                } elseif ($dueAt->isToday()) {
                                    $dueMetaText = 'Due Today';
                                } elseif ($dueAt->isTomorrow()) {
                                    $dueMetaText = 'Due Tomorrow';
                                } else {
                                    $daysLeft = now()
                                        ->startOfDay()
                                        ->diffInDays($dueAt->copy()->startOfDay(), false);
                                    if ($daysLeft <= 7) {
                                        $dueMetaText = $daysLeft . ' days left';
                                    } else {
                                        $dueMetaText = 'Due ' . $dueAt->format('M d');
                                    }
                                }
                            }

                        @endphp
                        <article
                            class="task-row flex items-start gap-2.5 rounded-xl border border-border-dark bg-surface-dark/20 px-3 py-2.5">
                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="task-toggle-form">
                                @csrf
                                <button type="submit" data-done="{{ $task->status === 'completed' ? '1' : '0' }}"
                                    class="cb-btn mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border {{ $isCompleted ? 'border-violet-500 bg-violet-600 text-white' : 'border-border-dark bg-transparent' }}">
                                    @if ($isCompleted)
                                        <span class="material-symbols-outlined block leading-none text-[12px]"
                                            style="font-variation-settings:'FILL' 1,'wght' 600,'GRAD' 0,'opsz' 24">check</span>
                                    @endif
                                </button>
                            </form>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p
                                        class="task-title truncate text-sm leading-tight font-medium {{ $isCompleted ? 'line-through text-app-muted' : 'text-slate-100' }}">
                                        {{ $task->title }}
                                    </p>
                                </div>

                                <p class="task-meta mt-0.5 truncate text-xs text-app-muted">
                                    @if ($priority !== 'none')
                                        <span
                                            class="inline-flex rounded-md px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $priorityClass }}">
                                            {{ strtoupper($priority) }}
                                        </span>
                                    @endif
                                    @if ($dueMetaText)
                                        <span class="mx-1">•</span>
                                        <span class="{{ $dueMetaClass }}">{{ $dueMetaText }}</span>
                                    @endif
                                </p>
                            </div>

                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-md border border-red-500/30 px-2 py-0.5 text-[11px] font-medium text-red-300 hover:bg-red-500/10">Delete</button>
                            </form>
                        </article>
                    @empty
                        <div
                            class="rounded-xl border border-dashed border-border-dark px-4 py-10 text-center text-sm text-app-muted">
                            No tasks yet.</div>
                    @endforelse
                </div>
            @else
                <div class="space-y-2">
                    @forelse ($projects as $project)
                        <a href="{{ route('dashboard', ['project' => $project->id]) }}"
                            class="flex items-center gap-3 rounded-xl border border-border-dark bg-surface-dark/20 px-4 py-3 hover:bg-surface-dark/35 transition">
                            <span
                                class="material-symbols-outlined text-[18px] text-app-muted">{{ $project->icon ?? 'folder' }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $project->name }}</p>
                                <p class="truncate text-xs text-app-muted">{{ $project->description ?: 'No description' }}
                                </p>
                            </div>
                            @if (($project->tasks_count ?? 0) > 0)
                                <span
                                    class="flex h-6 min-w-6 items-center justify-center rounded-full bg-surface-dark px-2 text-xs text-app-muted">{{ $project->tasks_count }}</span>
                            @endif
                        </a>
                    @empty
                        <div
                            class="rounded-xl border border-dashed border-border-dark px-4 py-10 text-center text-sm text-app-muted">
                            No projects yet. Create one from sidebar.</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
@endsection
