@extends('layouts.app')

@section('title', $selectedProject->name . ' Tasks')

@section('main')
    <header
        class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3 border-b border-border-dark bg-background-dark/80 px-4 sm:px-6 backdrop-blur-md">
        <button onclick="toggleSidebar()"
            class="lg:hidden p-1.5 rounded-lg hover:bg-surface-dark text-app-muted transition-colors shrink-0">
            <span class="material-symbols-outlined text-xl">menu</span>
        </button>

        <a href="{{ route('dashboard') }}"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg hover:bg-surface-dark text-app-muted transition-colors shrink-0">
            <span class="material-symbols-outlined text-xl leading-none">arrow_back</span>
        </a>
        <div class="flex h-8 items-center gap-2 min-w-0">
            <h2 class="inline-flex h-8 items-center text-sm sm:text-base font-bold leading-none truncate">
                {{ $selectedProject->name }}
            </h2>
            <span
                class="hidden sm:inline-flex h-5 items-center self-center rounded-full border border-border-dark bg-surface-dark px-2 text-[10px] font-semibold uppercase tracking-wider text-app-muted shrink-0">Active</span>
            @if (!empty($selectedProject->description))
                <p
                    class="hidden sm:inline-flex h-8 items-center text-[11px] leading-none text-app-muted truncate max-w-[280px] md:max-w-[420px]">
                    {{ \Illuminate\Support\Str::limit($selectedProject->description, 80) }}
                </p>
            @endif
        </div>
        <div class="ml-auto flex items-center gap-1 shrink-0">
            <a href="{{ route('projects.settings', $selectedProject->id) }}"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs text-app-muted hover:bg-surface-dark hover:text-app-text transition-colors">
                <span class="material-symbols-outlined text-[16px]">settings</span>
                <span class="hidden sm:inline">Settings</span>
            </a>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
        <div class="mx-auto w-full max-w-4xl">
            @if (session('status'))
                <x-alert type="success">{{ session('status') }}</x-alert>
            @endif

            <form action="{{ route('tasks.store', $selectedProject->id) }}" method="POST"
                class="mb-4 flex flex-col gap-3 rounded-xl border border-border-dark bg-surface-dark/40 p-3">
                @csrf
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Add task"
                    class="w-full rounded-lg border-none bg-transparent px-2 py-1 text-sm focus:ring-0 focus:outline-none placeholder:text-app-muted text-app-text">

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border-dark/50 pt-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Priority Dropdown -->
                        <div class="relative" x-data="{ open: false, priority: '{{ old('priority', 'none') }}' }">
                            <input type="hidden" name="priority" x-model="priority">
                            <button type="button" @click="open = !open" @click.away="open = false"
                                class="flex h-8 items-center gap-1.5 rounded-md border border-border-dark bg-background-dark px-2.5 text-xs font-medium text-app-muted hover:text-app-text hover:border-app-muted/50 transition-colors">
                                <span class="material-symbols-outlined text-[16px]"
                                    :class="{
                                        'text-red-500': priority === 'high',
                                        'text-yellow-500': priority === 'medium',
                                        'text-blue-500': priority === 'low',
                                        'text-app-muted': priority === 'none'
                                    }">low_priority</span>
                                <span
                                    x-text="priority === 'high' ? 'High' : (priority === 'medium' ? 'Medium' : (priority === 'low' ? 'Low' : 'Priority'))"></span>
                            </button>

                            <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                class="absolute left-0 top-full mt-1 z-50 w-36 rounded-lg border border-border-dark bg-[#0f1f35] p-1 shadow-xl origin-top-left">
                                <button type="button" @click="priority = 'none'; open = false"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark text-app-muted transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">priority</span> None
                                </button>
                                <button type="button" @click="priority = 'low'; open = false"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark text-blue-500 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">priority</span> Low
                                </button>
                                <button type="button" @click="priority = 'medium'; open = false"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark text-yellow-500 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">priority</span> Medium
                                </button>
                                <button type="button" @click="priority = 'high'; open = false"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark text-red-500 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">priority</span> High
                                </button>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <x-date-picker name="due_date" :value="old('due_date')" placeholder="Due date" />
                    </div>

                    <button type="submit"
                        class="h-8 rounded-md bg-primary px-4 text-xs font-semibold text-white hover:opacity-90 transition-opacity ml-auto">Add</button>
                </div>
            </form>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-3 text-xs text-app-muted">
                {{ $tasks->count() }} tasks
            </div>

            <div x-data="taskManager()" class="space-y-4">
                <style>
                    .filter-pending .task-row[data-status="completed"] {
                        display: none !important;
                    }

                    .filter-completed .task-row[data-status="pending"] {
                        display: none !important;
                    }
                </style>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <div class="flex items-center rounded-lg bg-surface-dark/40 p-1 border border-border-dark">
                        <button @click="setFilter('all')"
                            :class="filter === 'all' ? 'text-primary shadow-sm' :
                                'text-app-muted hover:bg-surface-dark'"
                            class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">All Tasks</button>
                        <button @click="setFilter('pending')"
                            :class="filter === 'pending' ? ' text-primary shadow-sm' :
                                'text-app-muted hover:bg-surface-dark'"
                            class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">Pending</button>
                        <button @click="setFilter('completed')"
                            :class="filter === 'completed' ? ' text-primary shadow-sm' :
                                'text-app-muted hover:bg-surface-dark'"
                            class="px-4 py-1.5 text-xs font-medium rounded-md transition-all">Completed</button>
                    </div>
                    <div class="relative shrink-0" x-data="{ sortOpen: false }">
                        <button @click="sortOpen = !sortOpen" @click.away="sortOpen = false"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-app-muted hover:text-app-text transition-colors rounded-lg hover:bg-surface-dark/40">
                            <span class="material-symbols-outlined text-[16px]">sort</span>
                            <span>Sort by: <span
                                    x-text="sort === 'priority' ? 'Priority' : (sort === 'name' ? 'Name' : 'Time')"
                                    class="text-app-text"></span></span>
                        </button>
                        <div x-show="sortOpen" style="display: none;" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                            class="absolute right-0 top-full mt-1 z-50 w-36 rounded-lg border border-border-dark bg-[#0f1f35] p-1 shadow-xl origin-top-right">
                            <button @click="setSort('time'); sortOpen = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark transition-colors"
                                :class="sort === 'time' ? 'text-primary' : 'text-app-muted'">Time</button>
                            <button @click="setSort('priority'); sortOpen = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark transition-colors"
                                :class="sort === 'priority' ? 'text-primary' : 'text-app-muted'">Priority</button>
                            <button @click="setSort('name'); sortOpen = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-xs hover:bg-surface-dark transition-colors"
                                :class="sort === 'name' ? 'text-primary' : 'text-app-muted'">Name</button>
                        </div>
                    </div>
                </div>

                <div class="relative min-h-[100px]">
                    <!-- Local Loading Overlay for Tasks -->
                    <div x-show="isLocalLoading" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-background-dark/60 backdrop-blur-[2px] rounded-xl"
                        style="display: none;">
                        <div class="flex items-center gap-2 text-primary">
                            <span class="material-symbols-outlined animate-spin">progress_activity</span>
                            <span class="text-xs font-medium">Updating...</span>
                        </div>
                    </div>

                    <div class="space-y-2 transition-opacity duration-200" x-ref="taskList"
                        :class="['filter-' + filter, isLocalLoading ? 'opacity-50' : 'opacity-100']">
                        @forelse ($tasks as $task)
                            @php
                                $dueAt = $task->due_date;
                                $isCompleted = $task->status === 'completed';
                                $isPending = !$isCompleted;
                                $isOverdue = $dueAt && $isPending && $dueAt->isPast();

                                $priority = strtolower($task->priority ?? 'none');
                                $priorityClass = match ($priority) {
                                    'high' => 'bg-red-500/20 border-red-500/30 border-1 text-red-500',
                                    'medium' => 'bg-yellow-600/20 border-yellow-600/30 border-1 text-yellow-500',
                                    'low' => 'bg-blue-600/20 border-blue-600/30 border-1 text-blue-500',
                                    default => 'bg-slate-500/20 border-slate-500/30 border-1 text-slate-500',
                                };

                                $dueText = null;
                                if ($dueAt) {
                                    if ($dueAt->copy()->startOfDay()->isPast() && !$dueAt->isToday()) {
                                        $dueText = 'Overdue';
                                    } elseif ($dueAt->isToday()) {
                                        $dueText = 'Due Today';
                                    } elseif ($dueAt->isTomorrow()) {
                                        $dueText = 'Due Tomorrow';
                                    } else {
                                        $daysLeft = now()
                                            ->startOfDay()
                                            ->diffInDays($dueAt->copy()->startOfDay(), false);
                                        if ($daysLeft <= 7) {
                                            $dueText = $daysLeft . ' days left';
                                        } else {
                                            $dueText = 'Due ' . $dueAt->format('M d');
                                        }
                                    }
                                }
                                $completedText = $task->completed_at
                                    ? 'Completed ' . $task->completed_at->diffForHumans()
                                    : 'Completed just now';
                            @endphp
                            <article
                                class="task-row flex items-start gap-2.5 rounded-xl border border-border-dark bg-surface-dark/40 hover:bg-surface-dark/60 px-3 py-2.5 transition-opacity duration-200 {{ $isCompleted ? 'opacity-50' : '' }}"
                                data-status="{{ $isCompleted ? 'completed' : 'pending' }}"
                                data-priority="{{ $priority === 'high' ? 3 : ($priority === 'medium' ? 2 : ($priority === 'low' ? 1 : 0)) }}"
                                data-name="{{ strtolower($task->title) }}"
                                data-time="{{ $task->created_at->timestamp }}">
                                <form action="{{ route('tasks.toggle', $task->id) }}" method="POST"
                                    class="task-toggle-form" @submit.prevent="toggleTask($event, $el)">
                                    @csrf
                                    <button type="submit" data-done="{{ $task->status === 'completed' ? '1' : '0' }}"
                                        class="cb-btn mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition-colors {{ $isCompleted ? 'border-blue-500 bg-blue-600 text-white' : 'border-border-dark bg-transparent' }}">
                                        @if ($isCompleted)
                                            <span class="material-symbols-outlined block leading-none text-[10px]"
                                                style="font-variation-settings:'FILL' 1,'wght' 600,'GRAD' 0,'opsz' 24">check</span>
                                        @endif
                                    </button>
                                </form>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p @click="$event.target.closest('.task-row').querySelector('.task-toggle-form').dispatchEvent(new Event('submit', {cancelable: true}))"
                                            class="task-title truncate text-sm leading-tight font-medium cursor-pointer transition-colors {{ $isCompleted ? 'line-through text-app-muted' : 'text-white' }}">
                                            {{ $task->title }}
                                        </p>
                                    </div>

                                    <div
                                        class="task-meta mt-1 flex flex-wrap items-center gap-2 truncate text-xs text-app-muted">
                                        <span
                                            class="inline-flex rounded-sm px-1.5 py-0.5 text-center text-[10px] font-semibold uppercase tracking-wide {{ $priorityClass }}">
                                            {{ $priority === 'none' ? 'No Priority' : strtoupper($priority) . ' Priority' }}
                                        </span>

                                        <span
                                            class="due-date-display flex items-center gap-1 {{ $isCompleted ? 'hidden' : '' }}">
                                            @if ($dueText)
                                                <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                                <span>{{ $dueText }}</span>
                                            @endif
                                        </span>

                                        <span
                                            class="completed-date-display flex items-center gap-1 {{ !$isCompleted ? 'hidden' : '' }}">
                                            <span class="material-symbols-outlined text-[14px]">task_alt</span>
                                            <span class="completed-text">{{ $completedText }}</span>
                                        </span>
                                    </div>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('taskManager', () => ({
                filter: 'all',
                sort: 'time',
                isLocalLoading: false,
                init() {
                    this.sortTasks();
                },
                setFilter(val) {
                    this.isLocalLoading = true;
                    this.filter = val;
                    setTimeout(() => {
                        this.isLocalLoading = false;
                    }, 300);
                },
                setSort(val) {
                    this.isLocalLoading = true;
                    this.sort = val;
                    setTimeout(() => {
                        this.sortTasks();
                        this.isLocalLoading = false;
                    }, 300);
                },
                sortTasks() {
                    const container = this.$refs.taskList;
                    if (!container) return;
                    const tasks = Array.from(container.querySelectorAll('.task-row'));
                    tasks.sort((a, b) => {
                        if (this.sort === 'priority') {
                            return b.dataset.priority - a.dataset.priority;
                        } else if (this.sort === 'name') {
                            return a.dataset.name.localeCompare(b.dataset.name);
                        } else {
                            // time
                            return b.dataset.time - a.dataset.time;
                        }
                    });
                    tasks.forEach(task => container.appendChild(task));
                },
                toggleTask(event, form) {
                    const btn = form.querySelector('button');
                    const isCompleted = btn.dataset.done === '1';
                    const article = form.closest('.task-row');
                    const title = article.querySelector('.task-title');
                    const dueDateDisplay = article.querySelector('.due-date-display');
                    const completedDateDisplay = article.querySelector('.completed-date-display');
                    const completedText = article.querySelector('.completed-text');

                    // Optimistic UI update
                    if (isCompleted) {
                        btn.dataset.done = '0';
                        btn.classList.remove('border-blue-500', 'bg-blue-600', 'text-white');
                        btn.classList.add('border-border-dark', 'bg-transparent');
                        btn.innerHTML = '';
                        title.classList.remove('line-through', 'text-app-muted');
                        title.classList.add('text-white');
                        article.dataset.status = 'pending';
                        article.classList.remove('opacity-50');

                        if (dueDateDisplay) dueDateDisplay.classList.remove('hidden');
                        if (completedDateDisplay) completedDateDisplay.classList.add('hidden');
                    } else {
                        btn.dataset.done = '1';
                        btn.classList.remove('border-border-dark', 'bg-transparent');
                        btn.classList.add('border-blue-500', 'bg-blue-600', 'text-white');
                        btn.innerHTML =
                            '<span class="material-symbols-outlined block leading-none text-[10px]" style="font-variation-settings:\'FILL\' 1,\'wght\' 600,\'GRAD\' 0,\'opsz\' 24">check</span>';
                        title.classList.remove('text-white');
                        title.classList.add('line-through', 'text-app-muted');
                        article.dataset.status = 'completed';
                        article.classList.add('opacity-50');

                        if (dueDateDisplay) dueDateDisplay.classList.add('hidden');
                        if (completedDateDisplay) {
                            completedDateDisplay.classList.remove('hidden');
                            if (completedText) completedText.textContent = 'Completed just now';
                        }
                    }

                    // Send request in background
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }
            }));
        });
    </script>
@endpush
