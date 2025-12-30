@extends('emails.layouts.base')

@section('title', $ticket->id)

@section('preheader')
    Yeni Müşteri Mesajı. Talep No: ({{ $ticket->id }})
@endsection

@section('content')
    <h2>Yeni Müşteri Mesajı</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 16px 0;
    ">
        <span class="code">{{ $ticket->id }}</span> numaralı Destek Talebine yeni mesaj geldi. Lütfen mesaj ayrıntılarını görmek için Yönetim Paneli'ni ziyaret edin.
    </p>

    {{-- Divider --}}
    <div style="height:1px; background:#e6e8ec; margin:12px 0;"></div>

    <p style="margin:0 0 6px 0;"><strong>Konu:</strong> {{ $ticket->subject }}</p>
    <p style="margin:0 0 6px 0;"><strong>Müşteri:</strong> {{ $ticket->user?->name ?: '-' }}</p>
    <p style="margin:0 0 6px 0;"><strong>E-posta:</strong> {{ $ticket->user?->email ?: '-' }}</p>
    @if($ticket->order)
        <p style="margin:0 0 6px 0;"><strong>Sipariş:</strong> {{ $ticket->order->code ?: ('#'.$ticket->order_id) }}</p>
    @endif

    {{-- Divider --}}
    <div style="height:1px; background:#e6e8ec; margin:12px 0;"></div>

    <p style="margin:0 0 8px 0;"><strong>Mesaj:</strong></p>
    <p style="margin:0;">{{ $supportMessage->body }}</p>
@endsection
