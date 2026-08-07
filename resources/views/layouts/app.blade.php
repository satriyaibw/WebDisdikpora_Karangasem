<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') | {{ settings('site.name', config('app.name')) }}@else{{ settings('site.name', config('app.name')) }}@endif</title>
    <meta name="description" content="@yield('metaDescription', settings('site.tagline', ''))">
    <link rel="icon" href="{{ asset('images/disdikpora-favicon.svg') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    @php
        $ogSiteName = settings('site.name', config('app.name'));
        $ogTitle = $ogSiteName;
        $ogDescription = settings('site.tagline', '');
        $ogImage = url('images/disdikpora-logo.svg');

        $sections = app('view')->getSections();
        $yield = static fn (string $name, string $fallback): string => trim((string) ($sections[$name] ?? '')) !== ''
            ? app('view')->yieldContent($name)
            : $fallback;

        $ogType = $yield('og:type', 'website');
        $ogTitleMeta = $yield('ogTitle', $ogTitle);
        $ogDescriptionMeta = $yield('ogDescription', $ogDescription);
        $ogImageMeta = $yield('ogImage', $ogImage);
    @endphp

    <meta property="og:site_name" content="{{ $ogSiteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $ogTitleMeta }}">
    <meta property="og:description" content="{{ $ogDescriptionMeta }}">
    <meta property="og:image" content="{{ $ogImageMeta }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @stack('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <x-public-header />

    <main class="min-h-screen pb-14">
        @yield('content')
    </main>

    <x-public-footer />

    @livewireScripts
</body>
</html>
