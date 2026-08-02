<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') | {{ settings('site.name', config('app.name')) }}@else{{ settings('site.name', config('app.name')) }}@endif</title>
    <meta name="description" content="@yield('metaDescription', settings('site.tagline', ''))">
    <link rel="icon" href="{{ asset('images/disdikpora-favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <x-public-header />

    <main class="min-h-screen">
        @yield('content')
    </main>

    <x-public-footer />

    @livewireScripts
</body>
</html>
