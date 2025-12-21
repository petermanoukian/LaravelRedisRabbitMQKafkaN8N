<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.header')
</head>
<body>
    @include('includes.navigation')

    <main class="container py-4">
        @yield('content')
    </main>

    @include('includes.script.script')
</body>
</html>
