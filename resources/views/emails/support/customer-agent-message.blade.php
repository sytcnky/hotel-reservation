<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Destek yanıtı</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">
<h2 style="margin:0 0 12px 0;">
    Destek yanıtı
</h2>

<p style="margin:0 0 6px 0;">
    <strong>Talep:</strong> #{{ $ticket->id }}
</p>

<p style="margin:0 0 6px 0;">
    <strong>Konu:</strong> {{ $ticket->subject }}
</p>

<hr style="margin:14px 0;">

<p style="margin:0 0 8px 0;"><strong>Mesaj:</strong></p>
<p style="margin:0;">{{ $supportMessage->body }}</p>

</body>
</html>
