@php
    /** @var string $appName */
    $brand = $appName ?? config('app.name', 'CV');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' || app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brand }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        body { margin: 0; padding: 0; background: #f6f4ee; color: #130e21; font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(19,14,33,0.04); }
        .header { padding: 28px 32px 18px; text-align: {{ app()->getLocale() === 'ar' || app()->getLocale() === 'ur' ? 'right' : 'left' }}; }
        .header strong { font-size: 18px; color: #130e21; }
        .content { padding: 8px 32px 24px; line-height: 1.6; font-size: 15px; color: #2a2438; }
        .content h1 { font-size: 20px; margin: 0 0 16px; color: #130e21; }
        .content p { margin: 0 0 14px; }
        .button-wrap { padding: 8px 32px 24px; text-align: center; }
        .button {
            display: inline-block; padding: 14px 28px; background: #5c17e7; color: #ffffff !important;
            border-radius: 12px; font-weight: 600; text-decoration: none; font-size: 15px;
        }
        .muted { color: #6b6477; font-size: 13px; word-break: break-all; }
        .divider { height: 1px; background: #ece8de; margin: 8px 0 16px; }
        .footer { padding: 18px 32px 28px; font-size: 12px; color: #8a8499; text-align: center; }
        .quote { background: #f6f4ee; border-radius: 10px; padding: 14px 18px; margin: 8px 0 16px; }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f6f4ee; padding: 32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" class="container" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="header"><strong>{{ $brand }}</strong></td>
                    </tr>
                    <tr>
                        <td class="content">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            © {{ date('Y') }} {{ $brand }} · {{ __('messages.common.email_rights') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
