<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

        <title>
            @yield('title')
        </title>

        @vite('resources/css/app.css')
        <link rel="stylesheet" href="{{asset('Custom/fonts.css')}}">
        @yield('styles')
    </head>
    <body class="font-sans antialiased min-h-screen flex flex-col">
        @include('layouts.header')
        
        @yield('content')

        @include('layouts.footer')

        @yield('scripts')
        <script src="../Custom/header.js"></script>
    </body>
</html>
