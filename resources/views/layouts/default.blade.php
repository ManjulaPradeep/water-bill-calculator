<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Water Bill App')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(to bottom right, #ffffff, #f6eeff, #eee1fd);
        }
    </style>
</head>
<body class="antialiased text-gray-900">
    <div class="flex min-h-screen justify-center items-center">
        <main class="w-full max-w-md">
<div id="app">
    @yield('content')
</div>
        </main>
    </div>
</body>
</html>
