<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <script>
        (() => {
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            let storedTheme = null;

            try {
                storedTheme = window.localStorage.getItem('theme');
            } catch {
                // Use the system preference when browser storage is unavailable.
            }

            const theme = storedTheme === 'light' || storedTheme === 'dark'
                ? storedTheme
                : systemPrefersDark ? 'dark' : 'light';

            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.style.colorScheme = theme;
        })();
    </script>
    @routes
    @viteReactRefresh
    @vite(['resources/js/app.tsx', 'resources/css/app.css'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
