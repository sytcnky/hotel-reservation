<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Geri ödeme başarılı</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">

<h2 style="margin:0 0 12px 0;">Geri ödeme başarılı</h2>

<p style="margin:0 0 8px 0;">
    Sipariş kodu:
    <strong>{{ $order?->code ?? '-' }}</strong>
</p>

<p style="margin:0 0 8px 0;">
    İade tutarı:
    <strong>{{ number_format((float) ($refund->amount ?? 0), 2, ',', '.') }} {{ strtoupper((string) ($refund->currency ?? '')) }}</strong>
</p>

@if(!empty($refund->reason))
    <p style="margin:0 0 8px 0;">
        İade açıklaması:
        <strong>{{ $refund->reason }}</strong>
    </p>
@endif

<p style="margin:16px 0 0 0;">Teşekkürler.</p>

</body>
</html>
