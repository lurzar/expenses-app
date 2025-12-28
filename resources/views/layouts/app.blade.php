<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ url('images/favicon-white.png') }}">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        @livewireScripts
        <!-- Navbar -->
        @include('layouts.navigation')

        <div class="container mx-auto py-4">
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-base-200 shadow rounded-lg mb-4">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif
            <!-- Page Breadcrumb -->
            <div class="bg-primary text-black rounded-lg mb-4">
                <div class="px-4 sm:px-6 lg:px-8">
                    @include('layouts.breadcrumb')
                </div>
            </div>
            <!-- Page Content -->
            <main class="bg-base-200 overflow-hidden shadow rounded-lg">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
