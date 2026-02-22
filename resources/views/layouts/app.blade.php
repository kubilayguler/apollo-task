@php
    $navIcons = [
        'folder',
        'work',
        'home',
        'code',
        'star',
        'favorite',
        'rocket_launch',
        'school',
        'shopping_cart',
        'fitness_center',
        'build',
        'camera',
        'music_note',
        'sports_esports',
        'travel_explore',
        'account_balance',
        'analytics',
        'inventory_2',
        'task_alt',
        'bug_report',
        'science',
        'palette',
        'attach_money',
        'calendar_today',
        'local_shipping',
        'psychology',
        'security',
        'groups',
        'language',
        'lightbulb',
        'movie',
        'restaurant',
        'health_and_safety',
        'pets',
        'sports_soccer',
    ];
@endphp
<!DOCTYPE html>
<html class="dark" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Todo App')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        @media (max-width: 1023px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }
        }

        #sidebar {
            transition: transform .25s cubic-bezier(.4, 0, .2, 1);
        }

        .icon-opt {
            border-radius: .5rem;
            transition: background .1s;
        }

        .icon-opt:hover {
            background: #1e293b;
        }

        .icon-opt.chosen {
            background: #1e293b;
            outline: 2px solid #0b47a2;
        }
    </style>
    @stack('head')
</head>

<body class="m-0 h-screen overflow-hidden bg-background-dark text-app-text antialiased" x-data="app()"
    @keydown.escape.window="closeSidebar(); closeModal()">
    <div class="flex h-full w-full">
        <div x-show="sidebarOpen" @click="closeSidebar()"
            class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden" style="display:none"></div>

        <aside id="sidebar" :class="sidebarOpen ? 'open' : ''"
            class="fixed lg:relative z-40 flex w-72 h-full shrink-0 flex-col border-r border-border-dark bg-surface-dark">
            <div class="flex items-center justify-between p-5 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
                        <span class="material-symbols-outlined text-xl">person</span>
                    </div>
                    <h1 class="text-sm font-bold tracking-tight truncate max-w-[150px]">{{ auth()->user()->name }}</h1>
                </div>
                <button @click="closeSidebar()"
                    class="lg:hidden p-1 rounded-lg hover:bg-surface-dark text-app-muted transition-colors">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <div class="px-3 pb-4 shrink-0">
                <a href="/" @click="closeSidebar()"
                    class="group flex items-center gap-2.5 rounded-lg px-3 py-2 transition-colors text-app-text hover:bg-surface-dark">
                    <span
                        class="material-symbols-outlined text-[18px] shrink-0 text-app-muted group-hover:text-primary transition-colors">home</span>
                    <span class="flex-1 truncate text-sm font-medium">Home</span>
                </a>
            </div>

            <div class="px-3 flex-1 overflow-y-auto">
                <p class="mb-1.5 px-2 text-[10px] font-semibold uppercase tracking-widest text-app-muted">My Projects
                </p>
                <nav class="space-y-0.5">
                    @forelse ($projects ?? [] as $navProject)
                        @php $active = isset($selectedProject) && $selectedProject->id === $navProject->id; @endphp
                        <a href="{{ route('dashboard', ['project' => $navProject->id]) }}" @click="closeSidebar()"
                            class="group flex items-center gap-2.5 rounded-lg px-3 py-2 transition-colors {{ $active ? 'bg-primary/15 text-primary' : 'text-app-text hover:bg-surface-dark' }}">
                            <span
                                class="material-symbols-outlined text-[18px] group-hover:text-primary transition-colors shrink-0 {{ $active ? 'text-primary' : 'text-app-muted' }}">{{ $navProject->icon ?? 'folder' }}</span>
                            <span class="flex-1 truncate text-sm font-medium">{{ $navProject->name }}</span>
                            @if (($navProject->tasks_count ?? 0) > 0)
                                <span
                                    class="shrink-0 flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold tabular-nums {{ $active ? 'bg-primary text-white' : 'bg-surface-dark text-app-muted' }}">{{ $navProject->tasks_count }}</span>
                            @endif
                        </a>
                    @empty
                        <p class="px-3 py-2 text-xs italic text-app-muted">No projects yet.</p>
                    @endforelse

                    <button @click="openModal('new-project-modal')"
                        class="mt-1.5 flex w-full items-center gap-2.5 rounded-lg px-3 py-2 border border-dashed border-border-dark/70 text-app-muted hover:text-app-text hover:bg-surface-dark hover:border-border-dark transition-all text-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>New Project</span>
                    </button>
                </nav>
            </div>

            <div class="shrink-0 border-t border-border-dark p-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center justify-center gap-2 w-full p-2 rounded-lg bg-red-700/80 text-app-text hover:bg-red-700 transition-colors text-sm">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="relative flex min-w-0 flex-1 flex-col bg-background-dark overflow-hidden">
            <!-- Global Skeleton -->
            <div x-show="$store.global.loading" class="absolute inset-0 z-50 bg-background-dark flex flex-col">
                <header
                    class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3 border-b border-border-dark bg-background-dark/80 px-4 sm:px-6">
                    <div class="h-8 w-8 rounded-lg bg-surface-dark/40 animate-pulse"></div>
                    <div class="h-6 w-32 rounded bg-surface-dark/40 animate-pulse"></div>
                </header>
                <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
                    <div class="mx-auto w-full max-w-4xl space-y-4">
                        <div class="h-24 w-full rounded-xl bg-surface-dark/20 animate-pulse mb-6"></div>
                        <div class="space-y-2">
                            <template x-for="i in 4">
                                <div
                                    class="flex items-start gap-2.5 rounded-xl border border-border-dark bg-surface-dark/20 px-3 py-2.5 animate-pulse">
                                    <div class="mt-0.5 h-5 w-5 shrink-0 rounded-md bg-surface-dark"></div>
                                    <div class="min-w-0 flex-1 space-y-2 py-1">
                                        <div class="h-3 w-3/4 rounded bg-surface-dark"></div>
                                        <div class="h-2 w-1/4 rounded bg-surface-dark"></div>
                                    </div>
                                    <div class="h-5 w-12 rounded bg-surface-dark"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="!$store.global.loading" style="display: none;" class="flex-1 flex flex-col min-h-0">
                @yield('main')
            </div>
        </main>
    </div>

    <div id="new-project-modal" x-show="activeModal === 'new-project-modal'" x-on:click.self="closeModal()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        style="display:none">
        <div class="w-full max-w-lg rounded-2xl border border-border-dark bg-background-dark p-5 shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold">New Project</h3>
                <button @click="closeModal()"
                    class="rounded-lg p-1 text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label
                        class="block mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Name</label>
                    <input type="text" name="name" required placeholder="Project name"
                        class="w-full rounded-lg border border-border-dark bg-background-dark px-3 py-2 text-sm text-app-text placeholder:text-app-muted focus:border-primary focus:outline-none">
                </div>
                <x-icon-picker name="icon" :icons="$navIcons" />
                <div>
                    <label
                        class="block mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Description
                        <span class="normal-case font-normal opacity-60">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="What is this project about?"
                        class="w-full resize-none rounded-lg border border-border-dark bg-background-dark px-3 py-2 text-sm text-app-text placeholder:text-app-muted focus:border-primary focus:outline-none"></textarea>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-1">
                    <button type="button" @click="closeModal()"
                        class="w-full sm:w-auto rounded-lg border border-border-dark px-4 py-2 text-sm text-app-muted hover:text-app-text transition-colors">Cancel</button>
                    <button type="submit"
                        class="w-full sm:w-auto rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition-opacity">Create
                        Project</button>
                </div>
            </form>
        </div>
    </div>

    @stack('modals')

    <!-- AI Assistant Modal -->
    <div id="ai-modal" x-show="activeModal === 'ai-modal'" x-on:click.self="closeModal()"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        style="display:none" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-xl rounded-2xl border border-border-dark bg-background-dark shadow-2xl"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">auto_awesome</span>
                    <h3 class="text-sm font-semibold">AI Assistant</h3>
                </div>
                <button @click="closeModal()"
                    class="rounded-lg p-1 text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Command Input -->
            <div class="px-5 pb-4" x-data="aiCommand()">
                <form @submit.prevent="sendCommand()" class="relative">
                    <div
                        class="flex items-center gap-2 rounded-xl border border-border-dark bg-surface-dark/40 px-3 py-2 focus-within:border-primary/50 transition-colors">
                        <span class="material-symbols-outlined text-app-muted text-lg shrink-0">chat</span>
                        <input type="text" x-model="command" x-ref="aiInput"
                            placeholder='Try: "Plan my weekend trip and schedule it for Saturday"'
                            :disabled="loading"
                            class="flex-1 bg-transparent border-none text-sm text-app-text placeholder:text-app-muted focus:ring-0 focus:outline-none disabled:opacity-50">
                        <button type="submit" :disabled="!command.trim() || loading"
                            class="shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-lg bg-primary text-white hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-show="!loading" class="material-symbols-outlined text-lg">send</span>
                            <span x-show="loading"
                                class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                        </button>
                    </div>
                    <input type="hidden" x-model="projectId">
                </form>

                <!-- Quick Actions -->
                <div class="flex flex-wrap gap-1.5 mt-3" x-show="!loading && !result">
                    <button type="button"
                        @click="command = 'Create a grocery shopping list with common items'; $nextTick(() => sendCommand())"
                        class="inline-flex items-center gap-1 rounded-lg border border-border-dark bg-surface-dark/30 px-2.5 py-1.5 text-[11px] text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                        <span class="material-symbols-outlined text-[14px]">shopping_cart</span> Grocery list
                    </button>
                    <button type="button"
                        @click="command = 'Plan a productive morning routine with tasks'; $nextTick(() => sendCommand())"
                        class="inline-flex items-center gap-1 rounded-lg border border-border-dark bg-surface-dark/30 px-2.5 py-1.5 text-[11px] text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                        <span class="material-symbols-outlined text-[14px]">wb_sunny</span> Morning routine
                    </button>
                    <button type="button"
                        @click="command = 'Create tasks for a weekend house cleaning'; $nextTick(() => sendCommand())"
                        class="inline-flex items-center gap-1 rounded-lg border border-border-dark bg-surface-dark/30 px-2.5 py-1.5 text-[11px] text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                        <span class="material-symbols-outlined text-[14px]">cleaning_services</span> House cleaning
                    </button>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="mt-4 flex flex-col items-center gap-3 py-6">
                    <div class="relative">
                        <span
                            class="material-symbols-outlined text-primary text-3xl animate-spin">progress_activity</span>
                    </div>
                    <p class="text-xs text-app-muted animate-pulse">AI is thinking...</p>
                </div>

                <!-- Result -->
                <div x-show="result" x-cloak class="mt-4">
                    <!-- Success -->
                    <div x-show="result?.success" class="rounded-xl border border-green-500/30 bg-green-500/5 p-4">
                        <div class="flex items-start gap-2">
                            <span
                                class="material-symbols-outlined text-green-400 text-lg mt-0.5 shrink-0">check_circle</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-green-300" x-text="result?.message"></p>
                                <p class="text-xs text-app-muted mt-1">Action: <span class="text-app-text"
                                        x-text="formatAction(result?.action)"></span></p>

                                <!-- Created Tasks Preview -->
                                <div x-show="result?.data?.tasks?.length" class="mt-3 space-y-1.5">
                                    <p class="text-[10px] uppercase tracking-wider text-app-muted font-semibold">
                                        Created tasks</p>
                                    <template x-for="task in (result?.data?.tasks || [])" :key="task.id">
                                        <div
                                            class="flex items-center gap-2 rounded-lg bg-surface-dark/40 px-2.5 py-1.5">
                                            <span
                                                class="material-symbols-outlined text-primary text-sm">task_alt</span>
                                            <span class="text-xs text-app-text truncate" x-text="task.title"></span>
                                            <span x-show="task.subtasks?.length" class="text-[10px] text-app-muted"
                                                x-text="'+ ' + (task.subtasks?.length || 0) + ' subtasks'"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Updated Task Preview -->
                                <div x-show="result?.data?.task" class="mt-3 space-y-1.5">
                                    <p class="text-[10px] uppercase tracking-wider text-app-muted font-semibold">
                                        Updated task</p>
                                    <div class="flex items-center gap-2 rounded-lg bg-surface-dark/40 px-2.5 py-1.5">
                                        <span class="material-symbols-outlined text-primary text-sm">edit</span>
                                        <span class="text-xs text-app-text truncate"
                                            x-text="result?.data?.task?.title"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button @click="window.location.reload()"
                                class="flex-1 rounded-lg bg-primary/20 text-primary px-3 py-2 text-xs font-medium hover:bg-primary/30 transition-colors">Refresh
                                page</button>
                            <button @click="resetAi()"
                                class="rounded-lg border border-border-dark px-3 py-2 text-xs text-app-muted hover:text-app-text transition-colors">New
                                command</button>
                        </div>
                    </div>

                    <!-- Error -->
                    <div x-show="result && !result?.success"
                        class="rounded-xl border border-red-500/30 bg-red-500/5 p-4">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-red-400 text-lg mt-0.5 shrink-0">error</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-red-300"
                                    x-text="result?.message || 'Something went wrong'"></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button @click="resetAi()"
                                class="rounded-lg border border-border-dark px-3 py-2 text-xs text-app-muted hover:text-app-text transition-colors">Try
                                again</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Contextual Action Modal -->
    <div id="ai-task-modal" x-show="activeModal === 'ai-task-modal'" x-on:click.self="closeModal()"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        style="display:none" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-lg rounded-2xl border border-border-dark bg-background-dark shadow-2xl"
            x-data="aiTaskAction()" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">auto_awesome</span>
                    <h3 class="text-sm font-semibold">AI Task Action</h3>
                </div>
                <button @click="closeModal()"
                    class="rounded-lg p-1 text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <div class="px-5 pb-5">
                <!-- Task Info -->
                <div class="rounded-lg bg-surface-dark/40 border border-border-dark px-3 py-2 mb-4"
                    x-show="taskTitle">
                    <p class="text-xs text-app-muted">Task</p>
                    <p class="text-sm font-medium text-app-text truncate" x-text="taskTitle"></p>
                </div>

                <!-- Action Buttons -->
                <div x-show="!loading && !result" class="space-y-2">
                    <p class="text-[10px] uppercase tracking-wider text-app-muted font-semibold mb-2">Choose an action
                    </p>
                    <button @click="runAction('break_down')"
                        class="w-full flex items-center gap-3 rounded-xl border border-border-dark bg-surface-dark/30 px-4 py-3 text-left hover:bg-surface-dark/60 hover:border-primary/30 transition-all group">
                        <span class="material-symbols-outlined text-primary text-xl">account_tree</span>
                        <div>
                            <p class="text-sm font-medium text-app-text group-hover:text-primary transition-colors">
                                Break Down</p>
                            <p class="text-[11px] text-app-muted">Split into actionable sub-tasks</p>
                        </div>
                    </button>
                    <button @click="runAction('auto_schedule')"
                        class="w-full flex items-center gap-3 rounded-xl border border-border-dark bg-surface-dark/30 px-4 py-3 text-left hover:bg-surface-dark/60 hover:border-primary/30 transition-all group">
                        <span class="material-symbols-outlined text-yellow-500 text-xl">schedule</span>
                        <div>
                            <p class="text-sm font-medium text-app-text group-hover:text-yellow-400 transition-colors">
                                Auto Schedule</p>
                            <p class="text-[11px] text-app-muted">Set smart due dates automatically</p>
                        </div>
                    </button>
                    <button @click="runAction('optimize')"
                        class="w-full flex items-center gap-3 rounded-xl border border-border-dark bg-surface-dark/30 px-4 py-3 text-left hover:bg-surface-dark/60 hover:border-primary/30 transition-all group">
                        <span class="material-symbols-outlined text-green-500 text-xl">edit_note</span>
                        <div>
                            <p class="text-sm font-medium text-app-text group-hover:text-green-400 transition-colors">
                                Optimize</p>
                            <p class="text-[11px] text-app-muted">Improve title and description</p>
                        </div>
                    </button>


                </div>

                <!-- Loading -->
                <div x-show="loading" class="flex flex-col items-center gap-3 py-8">
                    <span class="material-symbols-outlined text-primary text-3xl animate-spin">progress_activity</span>
                    <p class="text-xs text-app-muted animate-pulse">AI is processing your task...</p>
                </div>

                <!-- Result -->
                <div x-show="result" x-cloak>
                    <div x-show="result?.success" class="rounded-xl border border-green-500/30 bg-green-500/5 p-4">
                        <div class="flex items-start gap-2">
                            <span
                                class="material-symbols-outlined text-green-400 text-lg mt-0.5 shrink-0">check_circle</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-green-300" x-text="result?.message"></p>
                                <p class="text-xs text-app-muted mt-1">Action: <span class="text-app-text"
                                        x-text="formatAction(result?.action)"></span></p>

                                <!-- Subtasks Preview -->
                                <div x-show="result?.data?.task?.subtasks?.length" class="mt-3 space-y-1">
                                    <p class="text-[10px] uppercase tracking-wider text-app-muted font-semibold">
                                        Sub-tasks</p>
                                    <template x-for="st in (result?.data?.task?.subtasks || [])"
                                        :key="st.id">
                                        <div
                                            class="flex items-center gap-2 rounded-lg bg-surface-dark/40 px-2.5 py-1.5">
                                            <span
                                                class="material-symbols-outlined text-primary text-sm">subdirectory_arrow_right</span>
                                            <span class="text-xs text-app-text truncate" x-text="st.title"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button @click="window.location.reload()"
                                class="w-full rounded-lg bg-primary/20 text-primary px-3 py-2 text-xs font-medium hover:bg-primary/30 transition-colors">Refresh
                                page</button>
                        </div>
                    </div>
                    <div x-show="result && !result?.success"
                        class="rounded-xl border border-red-500/30 bg-red-500/5 p-4">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-red-400 text-lg mt-0.5 shrink-0">error</span>
                            <p class="text-sm text-red-300" x-text="result?.message || 'Something went wrong'"></p>
                        </div>
                        <div class="mt-3">
                            <button @click="result = null"
                                class="rounded-lg border border-border-dark px-3 py-2 text-xs text-app-muted hover:text-app-text transition-colors">Try
                                again</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- AI Home Planner Modal -->
    <div id="ai-home-modal" x-show="activeModal === 'ai-home-modal'" x-on:click.self="closeModal()"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        style="display:none" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-lg rounded-2xl border border-border-dark bg-background-dark shadow-2xl"
            x-data="aiHome()" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <div class="flex items-center gap-2">
                    <span
                        class="material-symbols-outlined text-xl bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text"
                        style="-webkit-text-fill-color:transparent">auto_awesome</span>
                    <h3 class="text-sm font-semibold">AI Planner</h3>
                </div>
                <button @click="closeModal()"
                    class="rounded-lg p-1 text-app-muted hover:text-app-text hover:bg-surface-dark transition-colors">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <div class="px-5 pb-5">
                <!-- Quick Actions -->
                <div x-show="!loading && !result" class="space-y-4">
                    <!-- Global Actions -->
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-app-muted font-semibold mb-2">Quick Actions
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="runQuick('Create a daily morning routine project with tasks like wake up, exercise, breakfast, review daily goals, and set priorities for the day')"
                                class="flex items-center gap-2 rounded-xl border border-border-dark bg-surface-dark/30 px-3 py-2.5 text-left hover:bg-surface-dark/60 hover:border-yellow-500/30 transition-all group">
                                <span class="material-symbols-outlined text-yellow-400 text-lg">wb_sunny</span>
                                <div>
                                    <p
                                        class="text-xs font-medium text-app-text group-hover:text-yellow-300 transition-colors">
                                        Morning Routine</p>
                                    <p class="text-[10px] text-app-muted">Daily habit tasks</p>
                                </div>
                            </button>
                            <button
                                @click="runQuick('Create a weekly fitness plan project with workout tasks for each day including warm-up, exercises, and cool-down as subtasks')"
                                class="flex items-center gap-2 rounded-xl border border-border-dark bg-surface-dark/30 px-3 py-2.5 text-left hover:bg-surface-dark/60 hover:border-green-500/30 transition-all group">
                                <span class="material-symbols-outlined text-green-400 text-lg">fitness_center</span>
                                <div>
                                    <p
                                        class="text-xs font-medium text-app-text group-hover:text-green-300 transition-colors">
                                        Fitness Plan</p>
                                    <p class="text-[10px] text-app-muted">Weekly workouts</p>
                                </div>
                            </button>
                            <button
                                @click="runQuick('Create a grocery shopping project with categorized tasks like fruits, vegetables, dairy, meat, snacks, and household items')"
                                class="flex items-center gap-2 rounded-xl border border-border-dark bg-surface-dark/30 px-3 py-2.5 text-left hover:bg-surface-dark/60 hover:border-orange-500/30 transition-all group">
                                <span class="material-symbols-outlined text-orange-400 text-lg">shopping_cart</span>
                                <div>
                                    <p
                                        class="text-xs font-medium text-app-text group-hover:text-orange-300 transition-colors">
                                        Grocery List</p>
                                    <p class="text-[10px] text-app-muted">Shopping tasks</p>
                                </div>
                            </button>
                            <button
                                @click="runQuick('Create a study plan project for exam preparation with daily study sessions, topics to review, practice tests, and revision tasks spread across the next 2 weeks')"
                                class="flex items-center gap-2 rounded-xl border border-border-dark bg-surface-dark/30 px-3 py-2.5 text-left hover:bg-surface-dark/60 hover:border-purple-500/30 transition-all group">
                                <span class="material-symbols-outlined text-purple-400 text-lg">school</span>
                                <div>
                                    <p
                                        class="text-xs font-medium text-app-text group-hover:text-purple-300 transition-colors">
                                        Study Plan</p>
                                    <p class="text-[10px] text-app-muted">Exam preparation</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-px bg-border-dark"></div>
                        <span class="text-[10px] text-app-muted uppercase tracking-wider">or describe your own</span>
                        <div class="flex-1 h-px bg-border-dark"></div>
                    </div>

                    <!-- Custom Prompt -->
                    <form @submit.prevent="sendCustom()">
                        <div
                            class="flex items-center gap-2 rounded-xl border border-border-dark bg-surface-dark/40 px-3 py-2 focus-within:border-primary/50 transition-colors">
                            <span class="material-symbols-outlined text-app-muted text-lg shrink-0">chat</span>
                            <input type="text" x-model="customPrompt" x-ref="homeAiInput"
                                placeholder='e.g. "Create a travel packing list for my Europe trip"'
                                class="flex-1 bg-transparent border-none text-sm text-app-text placeholder:text-app-muted focus:ring-0 focus:outline-none">
                            <button type="submit" :disabled="!customPrompt.trim()"
                                class="shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-lg">send</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Loading -->
                <div x-show="loading" class="flex flex-col items-center gap-3 py-8">
                    <span
                        class="material-symbols-outlined text-3xl animate-spin bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text"
                        style="-webkit-text-fill-color:transparent">progress_activity</span>
                    <p class="text-xs text-app-muted animate-pulse">AI is planning...</p>
                </div>

                <!-- Result -->
                <div x-show="result" x-cloak>
                    <div x-show="result?.success" class="rounded-xl border border-green-500/30 bg-green-500/5 p-4">
                        <div class="flex items-start gap-2">
                            <span
                                class="material-symbols-outlined text-green-400 text-lg mt-0.5 shrink-0">check_circle</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-green-300" x-text="result?.message"></p>
                                <div x-show="result?.data?.tasks?.length" class="mt-3 space-y-1.5">
                                    <p class="text-[10px] uppercase tracking-wider text-app-muted font-semibold">
                                        Created tasks</p>
                                    <template x-for="task in (result?.data?.tasks || [])" :key="task.id">
                                        <div
                                            class="flex items-center gap-2 rounded-lg bg-surface-dark/40 px-2.5 py-1.5">
                                            <span
                                                class="material-symbols-outlined text-primary text-sm">task_alt</span>
                                            <span class="text-xs text-app-text truncate" x-text="task.title"></span>
                                            <span x-show="task.subtasks?.length" class="text-[10px] text-app-muted"
                                                x-text="'+ ' + (task.subtasks?.length || 0) + ' subtasks'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button @click="window.location.reload()"
                                class="flex-1 rounded-lg bg-primary/20 text-primary px-3 py-2 text-xs font-medium hover:bg-primary/30 transition-colors">Refresh
                                page</button>
                            <button @click="resetHome()"
                                class="rounded-lg border border-border-dark px-3 py-2 text-xs text-app-muted hover:text-app-text transition-colors">New
                                action</button>
                        </div>
                    </div>
                    <div x-show="result && !result?.success"
                        class="rounded-xl border border-red-500/30 bg-red-500/5 p-4">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-red-400 text-lg mt-0.5 shrink-0">error</span>
                            <p class="text-sm text-red-300" x-text="result?.message || 'Something went wrong'"></p>
                        </div>
                        <div class="mt-3">
                            <button @click="resetHome()"
                                class="rounded-lg border border-border-dark px-3 py-2 text-xs text-app-muted hover:text-app-text transition-colors">Try
                                again</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('global', {
                loading: true,
                setLoading(val) {
                    this.loading = val;
                }
            });

            Alpine.data('app', () => ({
                sidebarOpen: false,
                activeModal: null,
                init() {
                    setTimeout(() => {
                        Alpine.store('global').setLoading(false);
                    }, 300);

                    // Intercept link clicks to show skeleton
                    document.addEventListener('click', (e) => {
                        const link = e.target.closest('a');
                        if (link && link.href && !link.href.startsWith('#') && !link.href
                            .startsWith('javascript:') && link.target !== '_blank' && !e
                            .ctrlKey && !e.metaKey) {
                            try {
                                // Only show loading if it's a same-origin navigation
                                if (new URL(link.href).origin === window.location.origin) {
                                    Alpine.store('global').setLoading(true);
                                }
                            } catch (err) {
                                // Ignore invalid URLs
                            }
                        }
                    });

                    // Intercept form submissions to show skeleton
                    document.addEventListener('submit', (e) => {
                        // Don't show skeleton for background forms (like task toggle)
                        if (!e.target.classList.contains('task-toggle-form')) {
                            Alpine.store('global').setLoading(true);
                        }
                    });
                },
                openSidebar() {
                    this.sidebarOpen = true;
                },
                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },
                closeSidebar() {
                    this.sidebarOpen = false;
                },
                openModal(id) {
                    this.activeModal = id;
                },
                closeModal() {
                    this.activeModal = null;
                },
            }));

            // ── AI Command Bar (Text-to-Action) ──────────────────────
            Alpine.data('aiCommand', () => ({
                command: '',
                projectId: '',
                loading: false,
                result: null,
                init() {
                    // Try to get current project ID from the page URL
                    const params = new URLSearchParams(window.location.search);
                    this.projectId = params.get('project') || '';
                },
                async sendCommand() {
                    if (!this.command.trim() || this.loading) return;
                    this.loading = true;
                    this.result = null;
                    try {
                        const res = await fetch('/api/ai/text-command', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                command: this.command,
                                project_id: this.projectId ? parseInt(this
                                    .projectId) : null,
                            }),
                        });
                        this.result = await res.json();
                    } catch (err) {
                        this.result = {
                            success: false,
                            message: 'Network error. Please check your connection.'
                        };
                    } finally {
                        this.loading = false;
                    }
                },
                resetAi() {
                    this.command = '';
                    this.result = null;
                    this.$nextTick(() => this.$refs.aiInput?.focus());
                },
                formatAction(action) {
                    if (!action) return '';
                    return action.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                },
            }));

            // ── AI Home Planner ───────────────────────────────────
            Alpine.data('aiHome', () => ({
                customPrompt: '',
                loading: false,
                result: null,
                async runQuick(prompt) {
                    if (this.loading) return;
                    this.loading = true;
                    this.result = null;
                    try {
                        const res = await fetch('/api/ai/text-command', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                command: prompt
                            }),
                        });
                        this.result = await res.json();
                    } catch (err) {
                        this.result = {
                            success: false,
                            message: 'Network error. Please check your connection.'
                        };
                    } finally {
                        this.loading = false;
                    }
                },
                async sendCustom() {
                    if (!this.customPrompt.trim() || this.loading) return;
                    await this.runQuick(this.customPrompt);
                },
                resetHome() {
                    this.customPrompt = '';
                    this.result = null;
                },
            }));

            // ── AI Task Action (Contextual) ──────────────────────────
            Alpine.data('aiTaskAction', () => ({
                taskId: null,
                taskTitle: '',
                extraContext: '',
                loading: false,
                result: null,
                init() {
                    window.addEventListener('open-ai-task', (e) => {
                        this.taskId = e.detail.id;
                        this.taskTitle = e.detail.title;
                        this.extraContext = '';
                        this.loading = false;
                        this.result = null;
                        // Open the modal via parent scope
                        const appData = Alpine.$data(document.querySelector(
                        '[x-data="app()"]'));
                        if (appData) appData.activeModal = 'ai-task-modal';
                    });
                },
                async runAction(hint) {
                    if (!this.taskId || this.loading) return;
                    this.loading = true;
                    this.result = null;
                    try {
                        const res = await fetch(`/api/ai/tasks/${this.taskId}/action`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                action_hint: hint,
                                extra_context: this.extraContext,
                            }),
                        });
                        this.result = await res.json();
                    } catch (err) {
                        this.result = {
                            success: false,
                            message: 'Network error. Please check your connection.'
                        };
                    } finally {
                        this.loading = false;
                    }
                },
                formatAction(action) {
                    if (!action) return '';
                    return action.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                },
            }));
        });

        window.openSidebar = () => {
            const data = window.Alpine?.$data(document.body);
            if (data?.openSidebar) data.openSidebar();
        };

        window.toggleSidebar = () => {
            const data = window.Alpine?.$data(document.body);
            if (data?.toggleSidebar) data.toggleSidebar();
        };

        function selectIcon(name, pickerId) {
            const picker = document.getElementById(pickerId || 'icon-picker-default');
            if (!picker) return;
            picker.querySelector('input[name="icon"]').value = name;
            picker.querySelector('.icon-preview-sym').textContent = name;
            picker.querySelector('.icon-preview-lbl').textContent = name.replace(/_/g, ' ');
            picker.querySelectorAll('.icon-opt').forEach(button => button.classList.remove('chosen'));
            picker.querySelector(`.icon-opt[data-icon="${name}"]`)?.classList.add('chosen');
        }

        function clearDate(btn) {
            btn.closest('.relative')?.querySelector('[data-datepicker]')?._flatpickr?.clear();
            btn.classList.add('hidden');
        }
    </script>
    @stack('scripts')
</body>

</html>
