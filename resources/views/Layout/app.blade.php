<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'My Website')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen bg-center bg-fixed bg-no-repeat"
    style="
        background-image: url('{{ asset('images/background.png') }}');
        background-size: cover;
    "
>
<x-navbar :page="$page ?? ''" />


    <main>
        @yield('content')
    </main>

</body>

</html>
