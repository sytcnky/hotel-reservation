@extends('emails.layouts.base')

@section('title', $order->code)

@section('preheader')
    Yeni Sipariş! ({{ $order->code }})
@endsection

@section('content')

    <h2>Yeni Sipariş!</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 30px 0;
    ">
        <span class="code">{{ $order->code }}</span> numaralı yeni bir sipariş oluşturuldu. Lütfen aksiyon almak için yönetim panelini ziyaret edin.
    </p>

    {{-- Divider --}}
    <div style="height:1px; background:#e6e8ec; margin:12px 0;"></div>

    <p style="margin:0 0 6px 0;"><strong>Müşteri:</strong> {{ $order->customer_name ?: '-' }}</p>
    <p style="margin:0 0 6px 0;"><strong>E-posta:</strong> {{ $order->customer_email ?: '-' }}</p>
    <p style="margin:0 0 6px 0;"><strong>Telefon:</strong> {{ $order->customer_phone ?: '-' }}</p>
    <p style="margin:0 0 6px 0;"><strong>İletişim Dili:</strong> {{ $order->locale ?: '-' }}</p>

    {{-- Divider --}}
    <div style="height:1px; background:#e6e8ec; margin:12px 0;"></div>

    @if(!empty($order->metadata['customer_note']))
        <div style="height:1px; background:#eef0f3; margin:12px 0;"></div>
        <p style="margin:0; font-size:14px; line-height:22px; color:#0f172a;">
            <strong>Müşteri Notu:</strong> {{ $order->metadata['customer_note'] }}
        </p>

        {{-- Divider --}}
        <div style="height:1px; background:#e6e8ec; margin:12px 0;"></div>

    @endif

    <h3>Sipariş Detayları</h3>
    @include('emails.partials.order-items', ['order' => $order])
@endsection
