@extends('layouts.app')

@section('title', 'Home')

@section('main')
    <header
        class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3 border-b border-border-dark bg-background-dark/80 px-4 sm:px-6 backdrop-blur-md">
        <button onclick="toggleSidebar()"
            class="lg:hidden p-1.5 rounded-lg hover:bg-surface-dark text-app-muted transition-colors shrink-0">
            <span class="material-symbols-outlined text-xl">menu</span>
        </button>

        <h2 class="text-sm sm:text-base font-bold">Home Dashboard</h2>
    </header>

    <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
        <div class="mx-auto w-full max-w-6xl">
            @if (session('status'))
                <x-alert type="success">{{ session('status') }}</x-alert>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Calendar -->
                <div class="lg:col-span-2">
                    <x-calendar :tasks="$allTasks" />
                </div>

                <!-- Right Column: Recent Tasks & Projects -->
                <div class="space-y-6">
                    <x-project-list :projects="$projects" />
                    <x-recent-tasks :tasks="$recentTasks" />
                </div>
            </div>
        </div>
    </div>
@endsection
