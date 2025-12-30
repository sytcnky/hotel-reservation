<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    <style>
        /* Typography (safe defaults) */
        body {
            margin: 0;
            padding: 0;
            background: #f6f7f9;
        }

        h1 {
            margin: 0 0 12px 0;
            font-family: Helvetica, Arial, sans-serif;;
            font-size: 32px;
            line-height: 36px;
            color: #111827;
            font-weight: 700;
        }

        h2 {
            margin: 0 0 10px 0;
            font-family: Helvetica, Arial, sans-serif;;
            font-size: 24px;
            line-height: 28px;
            color: #111827;
            font-weight: 700;
        }

        p, li {
            margin: 0 0 10px 0;
            font-family: Helvetica, Arial, sans-serif;;
            font-size: 14px;
            line-height: 22px;
            color: #374151;
        }

        table {
            font-family: Helvetica, Arial, sans-serif;;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        .muted {
            margin: 0 0 10px 0;
            font-family: Helvetica, Arial, sans-serif;;
            font-size: 12px;
            line-height: 18px;
            color: #6b7280;
        }

        .code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 13px;
            padding: 2px 6px;
            background: #f3f4f6;
            border-radius: 6px;
            color: #111827;
        }
    </style>
</head>

<body>
@php
    $appName = (string) config('app.name');
    $appUrl  = (string) config('app.url');

    $preheader = trim((string) $__env->yieldContent('preheader'));
@endphp

{{-- Preheader hidden text --}}
@if($preheader !== '')
    <div style="display:none; font-size:1px; color:#f6f7f9; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
        {{ $preheader }}
    </div>
@endif

@php
    /** @var string|null $layoutVariant */
    $layoutVariant = $layoutVariant ?? 'customer';

    $headerPartial = $layoutVariant === 'ops'
        ? 'emails.partials.ops-header'
        : 'emails.partials.header';

    $footerPartial = $layoutVariant === 'ops'
        ? 'emails.partials.ops-footer'
        : 'emails.partials.footer';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; font-family: Helvetica, Arial, sans-serif;;  background:#f6f7f9; padding:24px 12px;">
    <tr>
        <td align="center" valign="top">

            @include($headerPartial)

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="width:100%; max-width:820px; background:#ffffff; border:1px solid #e6e8ec; border-radius:12px; overflow:hidden;">
                <tr>
                    <td style="padding:34px 24px 24px 24px;">
                        @yield('content')
                    </td>
                </tr>
            </table>

            <div style="height:16px; line-height:16px; font-size:0;">&nbsp;</div>

            @include($footerPartial)

        </td>
    </tr>
</table>
</body>
</html>
