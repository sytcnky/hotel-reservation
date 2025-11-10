{{-- resources/views/layouts/account.blade.php --}}
@extends('layouts.app')

@section('content')

<section class="container py-4 mt-xl-4">

    @php
    // Sidebar'ı sayfa bazında kapatmak için:
    // @section('account_hide_sidebar', 'true')
    $hideSidebar = trim($__env->yieldContent('account_hide_sidebar', '')) === 'true';
    @endphp

    <div class="row g-4">
        @unless($hideSidebar)
        <aside class="col-lg-3">
            @include('partials.side-menu')
        </aside>
        @endunless

        <main class="{{ $hideSidebar ? 'col-12' : 'col-lg-9' }} ps-xl-5">
            @yield('account_content')
        </main>
    </div>
</section>
@endsection
