@php
    /** @var string $appName */
    /** @var string $appUrl */
@endphp

<table role="presentation"
       width="100%"
       cellpadding="0"
       cellspacing="0"
       border="0"
       style="width:100%; max-width:600px; margin:0 auto 16px auto;">
    <tr>
        <td align="center" style="padding:0 4px;">
            {{-- Logo / Brand --}}
            <a href="{{ $appUrl }}"
               style="
                   display:inline-block;
                   text-decoration:none;
                   line-height:1;
               ">
                <span style="
                    font-family: Helvetica, Arial, sans-serif;;
                    font-size:36px;
                    font-weight:700;
                    color:#1E3A5F;
                ">Icmeler</span><span style="
                    font-family: Helvetica, Arial, sans-serif;;
                    font-size:36px;
                    font-weight:700;
                    color:#F39C12;
                ">Online</span>
                <br>
                <span style="
                    font-family: Helvetica, Arial, sans-serif;;
                    font-size:14px;
                    font-style:italic;
                    color:#1E3A5F;
                ">
                    30 years serving icmeler…
                </span>
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
