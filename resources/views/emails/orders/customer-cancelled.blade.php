@extends('emails.layouts.base')

@section('title', $order->code)

@section('preheader')
    Siparişiniz  iptal edildi ({{ $order->code }})
@endsection

@section('content')
    <h2>Siparişiniz iptal edildi,</h2>

    <p style="
        font-family:Helvetica, Arial, sans-serif;
        font-size:14px;
        line-height:22px;
        color:#0f172a;
        margin:0 0 30px 0;
    ">
        <span class="code">{{ $order->code }}</span> numaralı sipariş iptal olmuştur. Bunun için üzüldük, ileride başka siparişlerinizde görüşmek üzere.
    </p>
@endsection
