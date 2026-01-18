<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TurAcenta')</title>

    {{-- Site tarafı: Bootstrap + site.css, tek entry --}}
    @vite(['resources/css/site.scss', 'resources/js/site/site.js'])


</head>
<body data-page="{{ $pageKey ?? '' }}">
@include('partials.header')

<main>@yield('content')</main>

@include('partials.footer')
@include('partials.mobile-bottom-nav')
</body>
</html>
