@extends('emails.layouts.base')

@section('title', $title)

@section('preheader')
    {{ $subject ?? 'Şifre sıfırlama' }}
@endsection

@section('content')
    @php
        /** @var \App\Models\User|null $authUser */
        $authUser = auth()->user();
        $displayName = $authUser?->first_name ?: $authUser?->name ?: '';
    @endphp

    <h2>{{ $subject ?? 'Şifre sıfırlama' }},</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 30px 0;
    ">
        Kısa bir süre önce hesabınız için şifrenizi sıfırlamayı talep ettiniz. Yenisini seçmek için aşağıdaki düğmeye tıklamanız yeterlidir.
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

