<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-zinc-100 text-zinc-900 antialiased">
        <div class="mx-auto flex min-h-dvh w-full max-w-lg flex-col px-6 py-8 sm:py-10">
            @if (Route::has('login'))
                <header class="flex shrink-0 items-center justify-end">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            wire:navigate
                            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 shadow-xs hover:border-zinc-400"
                        >
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            wire:navigate
                            class="text-sm font-medium text-zinc-600 hover:text-zinc-900"
                        >
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </header>
            @endif

            <main class="flex flex-1 flex-col items-center justify-center gap-8 py-10 text-center">
                <div class="flex flex-col items-center gap-4">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-white shadow-xs ring-1 ring-zinc-200">
                        <x-app-logo-icon class="size-9 fill-current text-zinc-900" />
                    </span>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">
                            {{ config('app.name') }}
                        </h1>
                        <p class="max-w-sm text-sm leading-relaxed text-zinc-600">
                            {{ __('Collect live stats, box scores, and team reports in one place.') }}
                        </p>
                    </div>
                </div>

                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800"
                    >
                        {{ __('Go to dashboard') }}
                    </a>
                @else
                    @if (Route::has('login'))
                        <div class="flex flex-col items-center gap-3">
                            <a
                                href="{{ route('login') }}"
                                wire:navigate
                                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800"
                            >
                                {{ __('Log in') }}
                            </a>
                            <p class="text-xs text-zinc-500">
                                {{ __('Sign in with your club account to continue.') }}
                            </p>
                        </div>
                    @endif
                @endauth
            </main>
        </div>
    </body>
</html>
