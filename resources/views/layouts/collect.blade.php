<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="h-dvh overflow-hidden bg-zinc-100 text-zinc-900 antialiased">
        <div class="fixed inset-0 z-50 hidden flex-col items-center justify-center gap-4 bg-white px-8 text-center text-zinc-900 min-[740px]:portrait:flex">
            <flux:icon name="device-tablet" class="size-12" />
            <p class="text-lg font-medium">{{ __('Turn the screen sideways') }}</p>
        </div>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
        <script>
            if (window.matchMedia('(min-width: 740px) and (orientation: portrait)').matches) {
                screen.orientation?.lock?.('landscape')?.catch(() => {});
            }
        </script>
    </body>
</html>
