@extends('emails.layouts.base')

@section('title', $title)

@section('preheader')
    {{ $subject ?? 'E-posta doğrulama' }}
@endsection

@section('content')
    @php
        /** @var \App\Models\User|null $authUser */
        $authUser = auth()->user();
        $displayName = $authUser?->first_name ?: $authUser?->name ?: '';
    @endphp

    <h2>Hoşgeldin {{ $displayName ?? 'E-posta doğrulama' }},</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 30px 0;
    ">
        Kaydolduğunuz için teşekkür ederiz. Başlamak için hesabını etkinleştirmen gerekiyor.
    </p>

    @component('emails.partials.banner', [
    'tone' => 'info',
    'ctaHref' => $actionUrl,
    'ctaLabel' => $actionText,
])
        <p style="margin:0;">
            {{ $intro ?? '' }}
        </p>
    @endcomponent

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:30px 0 0 0;
    ">
        {{ $outro ?? '' }}
    </p>
@endsection

