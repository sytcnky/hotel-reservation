<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->code }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">

<h2 style="margin:0 0 12px 0;">
    Siparişiniz iptal edildi
</h2>

<p style="margin:0 0 8px 0;">
    Sipariş kodu:
    <strong>{{ $order->code }}</strong>
</p>

@if(!empty($order->cancelled_reason))
    <p style="margin:0 0 8px 0;">
        Gerekçe:
        <strong>{{ $order->cancelled_reason }}</strong>
    </p>
@endif

<p style="margin:16px 0 0 0;">
    Sorunuz varsa bu e-postaya yanıt verebilirsiniz.
</p>

</body>
</html>
