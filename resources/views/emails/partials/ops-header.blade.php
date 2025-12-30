@php
    /** @var string $appName */
    /** @var string $appUrl */
@endphp

<table role="presentation"
       width="100%"
       cellpadding="0"
       cellspacing="0"
       border="0"
       style="width:100%; max-width:820px; margin:0 auto 16px auto;">
    <tr>
        <td style="padding:0 4px;">
            {{-- Logo / Brand --}}
            <a href="{{ $appUrl }}/admin"
               style="
                   display:inline-block;
                   text-decoration:none;
                   line-height:1;
               ">
                <span style="
                    font-family: Helvetica, Arial, sans-serif;;
                    font-size:14px;
                    font-style:italic;
                    color:#1E3A5F;
                ">
                    icmeleronline.com
                </span>
                <br>
                <span style="
                    font-family: Helvetica, Arial, sans-serif;;
                    font-size:36px;
                    font-weight:700;
                    color:#1E3A5F;
                ">Operasyon</span><span style="
                    font-family: Helvetica, Arial, sans-serif;;
                    font-size:36px;
                    font-weight:700;
                    color:#F39C12;
                ">Merkezi</span>
            </a>
        </td>

        <td align="right" style="padding:0 4px;">
            {{-- Opsiyonel sağ alan --}}
            @hasSection('header_right')
                <span class="muted" style="margin:0;">
                    @yield('header_right')
                </span>
            @endif
        </td>
    </tr>
</table>
