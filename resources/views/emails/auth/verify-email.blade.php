<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'E-posta doğrulama' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">
<h2 style="margin:0 0 12px 0;">{{ $title ?? 'E-posta doğrulama' }}</h2>

<p style="margin:0 0 12px 0;">
    {{ $intro ?? '' }}
</p>

<p style="margin:0 0 12px 0;">
    <a href="{{ $actionUrl }}">{{ $actionText ?? 'Devam et' }}</a>
</p>

<p style="margin:0;">
    {{ $outro ?? '' }}
</p>
</body>
</html>
