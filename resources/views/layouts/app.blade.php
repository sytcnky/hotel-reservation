<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TurAcenta')</title>

    @php
        use App\Models\Setting;
        $gaCode = trim((string) Setting::get('google_analytics_code', ''));
    @endphp

    {{-- Site tarafı: Bootstrap + site.css, tek entry --}}
    @vite(['resources/css/site.scss', 'resources/js/site/site.js'])

    @if ($gaCode !== '')
        {!! $gaCode !!}
    @endif
</head>
<body data-page="{{ $pageKey ?? '' }}">
@include('partials.header')

<main>
    @include('partials.alerts')
    @yield('content')
</main>

@include('partials.footer')
@include('partials.mobile-bottom-nav')
</body>
</html>
