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
