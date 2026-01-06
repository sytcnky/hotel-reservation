{{-- resources/views/pages/legal/show.blade.php --}}
@extends('layouts.app')

@php
    $loc = app()->getLocale();

    $page = \App\Models\StaticPage::query()
        ->where('key', $pageKey)
        ->where('is_active', true)
        ->first();

    $c = $page->content ?? [];

    $title = $c['title'][$loc] ?? '';
    $body  = $c['body'][$loc] ?? '';
@endphp

@section('title', $title)

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h1 class="display-5 fw-bold text-secondary">
                    {{ $title }}
                </h1>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            {!! $body !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
