<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni Destek Talebi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">
<h2 style="margin:0 0 12px 0;">Yeni destek talebi</h2>

<p style="margin:0 0 6px 0;"><strong>Talep No:</strong> #{{ $ticket->id }}</p>
<p style="margin:0 0 6px 0;"><strong>Konu:</strong> {{ $ticket->subject }}</p>
<p style="margin:0 0 6px 0;"><strong>Müşteri:</strong> {{ $ticket->user?->name ?: '-' }}</p>
<p style="margin:0 0 6px 0;"><strong>E-posta:</strong> {{ $ticket->user?->email ?: '-' }}</p>

@if($ticket->order)
    <p style="margin:0 0 6px 0;"><strong>Sipariş:</strong> {{ $ticket->order->code ?: ('#'.$ticket->order_id) }}</p>
@endif

<hr style="margin:14px 0;">

<p style="margin:0 0 8px 0;"><strong>İlk mesaj:</strong></p>
<p style="margin:0;">{{ $supportMessage->body }}</p>

</body>
</html>
