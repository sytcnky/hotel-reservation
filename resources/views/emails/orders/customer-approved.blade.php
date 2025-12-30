@extends('emails.layouts.base')

@section('title', $order->code)

@section('preheader')
    Siparişiniz onaylandı ({{ $order->code }})
@endsection

@section('content')
    <h2>Siparişiniz onaylandı, İyi Tatiller!</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 30px 0;
    ">
        <span class="code">{{ $order->code }}</span> numaralı siparişiniz onaylandı.
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

    {{-- Divider --}}
    <div style="height:1px; margin:12px 0;"></div>

    <h3>Sipariş Detayları</h3>
    @include('emails.partials.order-items', ['order' => $order])
@endsection
