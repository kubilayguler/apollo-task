@extends('layouts.app')

@section('title', 'Settings · ' . $project->name)

@section('main')

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

    {{-- Header --}}
    <header
        class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3
               border-b border-border-dark bg-background-dark/80 px-4 sm:px-6 backdrop-blur-md">
        <button onclick="toggleSidebar()" class="lg:hidden p-1.5 rounded-lg hover:bg-surface-dark text-app-muted">
            <span class="material-symbols-outlined text-xl">menu</span>
        </button>
        <a href="{{ route('dashboard', ['project' => $project->id]) }}"
            class="p-1.5 rounded-lg hover:bg-surface-dark text-app-muted transition-colors">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="flex items-center gap-2 min-w-0">
            <h2 class="text-sm font-bold truncate">{{ $project->name }}</h2>
        </div>
        <span
            class="hidden sm:inline-flex items-center rounded-full border border-border-dark bg-surface-dark
                 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted ml-1">
            Settings
        </span>
    </header>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
        <div class="mx-auto max-w-xl space-y-6">

            {{-- Flash alerts --}}
            @if (session('status'))
                <x-alert type="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert type="error" title="Please fix the following errors">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            {{-- ── Edit section ── --}}
            <section class="rounded-2xl border border-border-dark bg-surface-dark/40 overflow-hidden">
                <div class="px-5 py-4 border-b border-border-dark flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-app-muted">edit</span>
                    <h3 class="text-sm font-semibold">Edit Project Details</h3>
                </div>

                <form id="edit-form" action="{{ route('projects.update', $project->id) }}" method="POST"
                    class="px-5 py-5 space-y-5">
                    @csrf @method('PUT')

                    {{-- Name --}}
                    <div>
                        <label for="proj-name"
                            class="block mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Name</label>
                        <input id="proj-name" type="text" name="name" value="{{ old('name', $project->name) }}"
                            required
                            class="w-full rounded-lg border border-border-dark bg-background-dark
                                  px-3 py-2 text-sm text-app-text placeholder:text-app-muted
                                  focus:border-primary focus:outline-none transition-colors">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="proj-desc"
                            class="block mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted">
                            Description <span class="normal-case font-normal opacity-60">(optional)</span>
                        </label>
                        <textarea id="proj-desc" name="description" rows="3" placeholder="What is this project about?"
                            class="w-full resize-none rounded-lg border border-border-dark bg-background-dark
                                     px-3 py-2 text-sm text-app-text placeholder:text-app-muted
                                     focus:border-primary focus:outline-none transition-colors">{{ old('description', $project->description) }}</textarea>
                    </div>

                    {{-- Icon picker --}}
                    <div>
                        <label
                            class="block mb-2 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Icon</label>
                        <x-icon-picker name="icon" :current="$project->icon ?? 'folder'" :icons="$navIcons" id="icon-picker-settings" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2
                                   text-sm font-semibold text-white hover:opacity-90 transition-opacity">
                            <span class="material-symbols-outlined text-[17px]">save</span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </section>

            {{-- ── Danger zone ── --}}
            <section x-data="{ showDelete: false, typedName: '', confirmed: false }" class="rounded-2xl border border-red-500/20 bg-red-500/5 overflow-hidden">
                <div class="px-5 py-4 border-b border-red-500/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-red-400">warning</span>
                    <h3 class="text-sm font-semibold text-red-300">Danger Zone</h3>
                </div>
                <div class="px-5 py-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-app-text">Delete this project</p>
                            <p class="text-xs text-app-muted mt-0.5">Permanently removes the project and all its tasks. This
                                cannot be undone.</p>
                        </div>
                        <button type="button" @click="showDelete = !showDelete"
                            class="shrink-0 rounded-lg border border-red-500/40 px-4 py-2 text-sm
                                   text-red-400 hover:bg-red-500/10 transition-colors">
                            Delete Project
                        </button>
                    </div>

                    {{-- Confirmation panel --}}
                    <div x-show="showDelete" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 space-y-3">

                        <p class="text-xs text-red-300">
                            To confirm deletion, type the project name "
                            <strong class="font-semibold text-red-200 select-all">{{ $project->name }}</strong>
                            " below:
                        </p>

                        <input type="text" x-model="typedName"
                            @input="confirmed = (typedName.trim() === '{{ addslashes($project->name) }}')"
                            placeholder="Type project name to confirm…"
                            class="w-full rounded-lg border border-red-500/40 bg-background-dark
                                  px-3 py-2 text-sm text-app-text placeholder:text-app-muted
                                  focus:border-red-500 focus:outline-none transition-colors">

                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                            <button type="button" @click="showDelete = false; typedName = ''; confirmed = false"
                                class="rounded-lg border border-border-dark px-4 py-2 text-sm
                                       text-app-muted hover:text-app-text transition-colors">
                                Cancel
                            </button>

                            <form action="{{ route('projects.destroy', $project->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" :disabled="!confirmed"
                                    :class="confirmed
                                        ?
                                        'bg-red-600 hover:bg-red-700 cursor-pointer opacity-100' :
                                        'bg-red-900/40 cursor-not-allowed opacity-40'"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                                           rounded-lg px-4 py-2 text-sm font-semibold text-white transition-all">
                                    <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                                    Delete permanently
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

@endsection
