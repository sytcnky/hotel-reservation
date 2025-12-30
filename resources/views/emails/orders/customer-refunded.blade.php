@extends('emails.layouts.base')

@section('title', $refund->order?->code ?? '')

@section('preheader')
    {{ ($refund->order?->code ?? '') }} nolu siparişiniz için geri ödeme yapıldı
@endsection

@section('content')
    @php
        /** @var \App\Models\RefundAttempt $refund */
        $order = $refund->order;

        $amount = number_format((float) ($refund->amount ?? 0), 2, ',', '.');
        $currency = strtoupper((string) ($refund->currency ?? ''));
        $amountText = trim($amount . ($currency !== '' ? (' ' . $currency) : ''));
    @endphp

    <h2>Geri ödemeniz yapıldı,</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 14px 0;
    ">
        <span class="code">{{ $order?->code }}</span> nolu siparişiniz için
        <strong>{{ $amountText }}</strong> geri ödeme yapıldı.
    </p>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 14px 0;
    ">
        Bankanıza bağlı olarak 1-7 iş günü içinde tutar kartınıza yansıyabilir.
    </p>

    @component('emails.partials.banner', [
    'tone' => 'info',
    'ctaHref' => localized_route('account.bookings'),
    'ctaLabel' => 'Hesabım',
])
        <p style="margin:0;">
            Siparişinizin durumunu görüntülemek ve diğer işlemler için "Hesabım" sayfasını kullanabilirsiniz.
        </p>
    @endcomponent
@endsection
