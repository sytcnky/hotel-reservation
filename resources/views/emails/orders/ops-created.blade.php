<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni sipariş</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">
<h2 style="margin:0 0 12px 0;">Yeni sipariş</h2>

<p style="margin:0 0 6px 0;"><strong>Kod:</strong> {{ $order->code }}</p>
<p style="margin:0 0 6px 0;"><strong>ID:</strong> {{ $order->id }}</p>
<p style="margin:0 0 6px 0;"><strong>Müşteri:</strong> {{ $order->customer_name ?: '-' }}</p>
<p style="margin:0 0 6px 0;"><strong>E-posta:</strong> {{ $order->customer_email ?: '-' }}</p>
<p style="margin:0 0 6px 0;"><strong>Telefon:</strong> {{ $order->customer_phone ?: '-' }}</p>

<p style="margin:12px 0 6px 0;">
    <strong>Tutar:</strong>
    {{ number_format((float) $order->total_amount, 2, ',', '.') }}
    {{ strtoupper((string) $order->currency) }}
</p>

@if(!empty($order->discount_amount) && (float)$order->discount_amount > 0)
    <p style="margin:0 0 6px 0;">
        <strong>İndirim:</strong>
        {{ number_format((float) $order->discount_amount, 2, ',', '.') }}
        {{ strtoupper((string) $order->currency) }}
    </p>
@endif

<p style="margin:12px 0 0 0;">
    <strong>Locale:</strong> {{ $order->locale ?: '-' }}
</p>
</body>
</html>
