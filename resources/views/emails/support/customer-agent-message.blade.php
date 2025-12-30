@extends('emails.layouts.base')

@section('title', $ticket->id)

@section('preheader')
    Talebiniz yanıtlandı. Talep no: ({{ $ticket->id }})
@endsection

@section('content')
    <h2>Talebiniz yanıtlandı,</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 32px 0;
    ">
        <strong>"{{ $ticket->subject }}"</strong> başlıklı Destek Talebiniz yanıtlandı.
    </p>

    @component('emails.partials.banner', [
    'tone' => 'info',
    'ctaHref' => localized_route('account.bookings'),
    'ctaLabel' => 'Yanıtı Gör',
])
        <p style="margin:0;">
            Mesaj içeriğini görmek için lütfen yanıtı gör butonunu kullanın.
        </p>
    @endcomponent

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:32px 0 0 0;
    ">
        Talep No: <span class="code">{{ $ticket->id }}</span>
    </p>
@endsection
