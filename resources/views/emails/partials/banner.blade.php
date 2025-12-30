@php
    /** @var string|null $tone */
    /** @var string|null $ctaHref */
    /** @var string|null $ctaLabel */

    $tone = is_string($tone ?? null) ? $tone : 'info';

    $tones = [
        'info' => [
            'bg' => '#e6eaef',
            'border' => '#dde2e8',
            'accent' => '#1E3A5F',
        ],
        'success' => [
            'bg' => '#e9f7ef',
            'border' => '#cfe9dc',
            'accent' => '#166534',
        ],
        'warning' => [
            'bg' => '#fff7ed',
            'border' => '#fed7aa',
            'accent' => '#9a3412',
        ],
    ];

    $t = $tones[$tone] ?? $tones['info'];
@endphp

<table role="presentation"
       width="100%"
       cellpadding="0"
       cellspacing="0"
       border="0"
       style="margin:0;">
    <tr>
        <td style="
            background:{{ $t['bg'] }};
            border:2px dashed {{ $t['border'] }};
            border-radius:10px;
            padding:16px;
        ">

            <div style="
                font-family:Helvetica, Arial, sans-serif;
                font-size:14px;
                line-height:22px;
                color:#0f172a;
                margin:0;
            ">
                {{ $slot }}
            </div>

            @if(!empty($ctaHref) && !empty($ctaLabel))
                <table role="presentation"
                       cellpadding="0"
                       cellspacing="0"
                       border="0"
                       style="margin:18px 0 0 0;">
                    <tr>
                        <td align="left">
                            <a href="{{ $ctaHref }}"
                               style="
                                   display:inline-block;
                                   background:{{ $t['accent'] }};
                                   color:#ffffff;
                                   text-decoration:none;
                                   padding:12px 28px;
                                   border-radius:6px;
                                   font-family:Helvetica, Arial, sans-serif;
                                   font-size:14px;
                                   font-weight:700;
                               ">
                                {{ $ctaLabel }}
                            </a>
                        </td>
                    </tr>
                </table>
            @endif

        </td>
    </tr>
</table>
