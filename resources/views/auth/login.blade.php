<!DOCTYPE html>
<html class="dark" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Todo App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="m-0 min-h-screen bg-background-dark text-app-text antialiased flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <!-- Logo / Brand -->
        <div class="mb-8 text-center">

            <h1 class="text-2xl font-bold tracking-tight">Kubilay's Todo App</h1>
            <p class="mt-1 text-sm text-app-muted">Sign in to continue to your tasks</p>
        </div>

        <!-- Card -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/40 p-6 shadow-xl backdrop-blur-sm">

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-300 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label
                        class="block mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Email</label>
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-app-muted">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="you@example.com"
                            class="w-full rounded-lg border border-border-dark bg-background-dark pl-10 pr-3 py-2.5 text-sm text-app-text placeholder:text-app-muted/60 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors">
                    </div>
                </div>

                <div>
                    <label
                        class="block mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Password</label>
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-app-muted">lock</span>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full rounded-lg border border-border-dark bg-background-dark pl-10 pr-3 py-2.5 text-sm text-app-text placeholder:text-app-muted/60 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors">
                    </div>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90 transition-opacity shadow-md shadow-primary/20 mt-2">
                    Sign In
                </button>
            </form>
        </div>

        <!-- Footer link -->
        <p class="mt-5 text-center text-xs text-app-muted">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-primary hover:underline font-medium">Create one</a>
        </p>
    </div>

</body>

</html>
