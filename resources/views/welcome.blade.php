<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TradeSchool') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased">

    <nav class="bg-amber-600 shadow">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <span class="text-white font-bold text-xl tracking-tight">{{ config('app.name') }}</span>
            <div class="flex gap-6 text-sm text-amber-100">
                <a href="{{ route('apply') }}" class="hover:text-white font-medium">Apply Now</a>
                <a href="{{ url('/admin') }}" class="hover:text-white">Admin Panel</a>
            </div>
        </div>
    </nav>

    <section class="max-w-5xl mx-auto px-4 py-20 text-center">
        <h1 class="text-5xl font-extrabold text-gray-900 leading-tight">
            Launch Your <span class="text-amber-600">Trade Career</span><br>Today
        </h1>
        <p class="mt-5 text-xl text-gray-500 max-w-2xl mx-auto">
            Enroll in industry-leading trade programs with flexible tuition payment plans.
            Get started in minutes — no prior experience required.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('apply') }}"
               class="bg-amber-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-amber-700 transition shadow-md">
                Apply for Enrollment
            </a>
            <a href="{{ url('/admin') }}"
               class="border-2 border-gray-300 text-gray-700 px-8 py-4 rounded-xl font-semibold text-lg hover:border-amber-500 hover:text-amber-600 transition">
                Admin Login
            </a>
        </div>
    </section>

    <section class="max-w-5xl mx-auto px-4 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="text-3xl mb-3">🔧</div>
                <h3 class="font-bold text-gray-900 text-lg mb-1">Industry Programs</h3>
                <p class="text-gray-500 text-sm">Electrical, HVAC, Plumbing, Welding, and more — accredited programs taught by working professionals.</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="text-3xl mb-3">💳</div>
                <h3 class="font-bold text-gray-900 text-lg mb-1">Flexible Payments</h3>
                <p class="text-gray-500 text-sm">Monthly, bi-weekly, or weekly payment plans. Pay on your schedule, not theirs.</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="text-3xl mb-3">📋</div>
                <h3 class="font-bold text-gray-900 text-lg mb-1">Simple Enrollment</h3>
                <p class="text-gray-500 text-sm">Apply online in minutes. Track your application status and get notified the moment you're approved.</p>
            </div>
        </div>
    </section>

    <footer class="text-center text-sm text-gray-400 py-8 border-t">
        &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Demo Application &mdash;
        <span class="italic">Architected by Matt Thatcher, implemented with Claude Code</span>
    </footer>

</body>
</html>
