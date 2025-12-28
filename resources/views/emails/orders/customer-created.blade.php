<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->code }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">

<h2 style="margin:0 0 12px 0;">
    Siparişiniz alındı
</h2>

<p style="margin:0 0 8px 0;">
    Sipariş kodu:
    <strong>{{ $order->code }}</strong>
</p>

<p style="margin:0 0 8px 0;">
    Toplam:
    <strong>{{ number_format((float) $order->total_amount, 2, ',', '.') }} {{ strtoupper((string) $order->currency) }}</strong>
</p>

@if(!empty($order->discount_amount) && (float)$order->discount_amount > 0)
    <p style="margin:0 0 8px 0;">
        İndirim:
        <strong>{{ number_format((float) $order->discount_amount, 2, ',', '.') }} {{ strtoupper((string) $order->currency) }}</strong>
    </p>
@endif

<p style="margin:16px 0 0 0;">
    Teşekkür ederiz.
</p>

</body>
</html>
