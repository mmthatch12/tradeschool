<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TradeSchool') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased">

    <nav class="bg-amber-600 shadow">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-white font-bold text-xl tracking-tight">
                {{ config('app.name', 'TradeSchool') }}
            </a>
            <div class="flex gap-6 text-sm text-amber-100">
                <a href="{{ route('apply') }}" class="hover:text-white">Apply Now</a>
                <a href="#" class="hover:text-white">Programs</a>
                <a href="{{ url('/admin') }}" class="hover:text-white">Admin</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-10">
        {{ $slot }}
    </main>

    <footer class="text-center text-sm text-gray-400 py-8 mt-12 border-t">
        &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Demo Application
    </footer>

    @livewireScripts
</body>
</html>
