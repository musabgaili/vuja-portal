{{-- Shared VujaDé payment email shell: fancier table layout, email-client safe. --}}
@php
    $brand = config('app.name', 'VujaDe');
    $primary = '#1B565E';
    $accent = '#0F969C';
    $soft = '#E8F3F4';
    $ink = '#1e293b';
    $muted = '#64748b';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $subjectLine ?? $brand }}</title>
</head>
<body style="margin:0;padding:0;background:{{ $soft }};font-family:Georgia,'Times New Roman',serif;color:{{ $ink }};">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $soft }};padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 40px rgba(27,86,94,.14);">
                {{-- Brand header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,{{ $primary }} 0%,{{ $accent }} 100%);padding:28px 32px 24px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="color:#ffffff;font-family:Arial,Helvetica,sans-serif;">
                                    <div style="font-size:11px;letter-spacing:.18em;text-transform:uppercase;opacity:.85;margin-bottom:8px;">VUJADE PLATFORM</div>
                                    <div style="font-size:26px;font-weight:700;letter-spacing:.02em;line-height:1.2;">{{ $brand }}</div>
                                </td>
                                <td align="right" valign="middle" style="font-family:Arial,Helvetica,sans-serif;">
                                    <div style="display:inline-block;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);color:#fff;font-size:11px;letter-spacing:.08em;text-transform:uppercase;padding:8px 12px;border-radius:999px;">
                                        {{ $badge ?? 'Payment' }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Accent strip --}}
                <tr>
                    <td style="height:4px;background:linear-gradient(90deg,#C9A24A 0%,{{ $accent }} 50%,{{ $primary }} 100%);font-size:0;line-height:0;">&nbsp;</td>
                </tr>

                {{-- Amount highlight --}}
                @isset($amountLabel)
                <tr>
                    <td style="padding:28px 32px 8px;font-family:Arial,Helvetica,sans-serif;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $soft }};border:1px solid #d5e8ea;border-radius:14px;">
                            <tr>
                                <td style="padding:18px 20px;">
                                    <div style="font-size:12px;color:{{ $muted }};letter-spacing:.06em;text-transform:uppercase;">{{ $amountCaption ?? 'Amount' }}</div>
                                    <div style="margin-top:6px;font-size:28px;font-weight:700;color:{{ $primary }};letter-spacing:.01em;">{{ $amountLabel }}</div>
                                    @isset($titleLabel)
                                        <div style="margin-top:8px;font-size:14px;color:{{ $ink }};">{{ $titleLabel }}</div>
                                    @endisset
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endisset

                {{-- English block --}}
                <tr>
                    <td style="padding:24px 32px 8px;font-family:Arial,Helvetica,sans-serif;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:{{ $accent }};margin-bottom:10px;">English</div>
                        <h1 style="margin:0 0 12px;font-size:20px;line-height:1.35;color:{{ $primary }};font-weight:700;">{{ $headingEn }}</h1>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:{{ $ink }};white-space:pre-line;">{{ $bodyEn }}</p>
                        @isset($noteEn)
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:{{ $muted }};">{{ $noteEn }}</p>
                        @endisset
                        @if(!empty($actionUrl) && !empty($actionTextEn))
                            <a href="{{ $actionUrl }}" style="display:inline-block;background:{{ $primary }};color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-size:14px;font-weight:700;letter-spacing:.02em;">
                                {{ $actionTextEn }}
                            </a>
                        @endif
                    </td>
                </tr>

                {{-- Divider --}}
                <tr>
                    <td style="padding:20px 32px;">
                        <div style="height:1px;background:linear-gradient(90deg,transparent,{{ $accent }},transparent);opacity:.45;"></div>
                    </td>
                </tr>

                {{-- Arabic block --}}
                <tr>
                    <td dir="rtl" style="padding:8px 32px 28px;font-family:Arial,Helvetica,sans-serif;text-align:right;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:{{ $accent }};margin-bottom:10px;">العربية</div>
                        <h1 style="margin:0 0 12px;font-size:20px;line-height:1.45;color:{{ $primary }};font-weight:700;">{{ $headingAr }}</h1>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:{{ $ink }};white-space:pre-line;">{{ $bodyAr }}</p>
                        @isset($noteAr)
                            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:{{ $muted }};">{{ $noteAr }}</p>
                        @endisset
                        @if(!empty($actionUrl) && !empty($actionTextAr))
                            <a href="{{ $actionUrl }}" style="display:inline-block;background:{{ $primary }};color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-size:14px;font-weight:700;">
                                {{ $actionTextAr }}
                            </a>
                        @endif
                    </td>
                </tr>

                {{-- Secondary CTA (optional register/login) --}}
                @if(!empty($secondaryUrl) && (!empty($secondaryTextEn) || !empty($secondaryTextAr)))
                <tr>
                    <td style="padding:0 32px 28px;font-family:Arial,Helvetica,sans-serif;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fafcfb;border:1px dashed #c5d9dc;border-radius:12px;">
                            <tr>
                                <td style="padding:16px 18px;">
                                    @if(!empty($secondaryTextEn))
                                        <p style="margin:0 0 10px;font-size:13px;line-height:1.55;color:{{ $muted }};">{{ $secondaryTextEn }}</p>
                                        <a href="{{ $secondaryUrl }}" style="display:inline-block;color:{{ $accent }};font-size:13px;font-weight:700;text-decoration:none;margin-bottom:12px;">{{ $secondaryCtaEn ?? 'Continue' }} →</a>
                                    @endif
                                    @if(!empty($secondaryTextAr))
                                        <p dir="rtl" style="margin:0 0 8px;font-size:13px;line-height:1.65;color:{{ $muted }};text-align:right;">{{ $secondaryTextAr }}</p>
                                        <p dir="rtl" style="margin:0;text-align:right;">
                                            <a href="{{ $secondaryUrl }}" style="color:{{ $accent }};font-size:13px;font-weight:700;text-decoration:none;">← {{ $secondaryCtaAr ?? 'متابعة' }}</a>
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                {{-- Footer --}}
                <tr>
                    <td style="padding:18px 32px 22px;background:#f8fafc;border-top:1px solid #e2e8f0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.55;color:#94a3b8;text-align:center;">
                        <div style="margin-bottom:4px;">{{ $brand }} · Secure payments</div>
                        <div dir="rtl">مدفوعات آمنة · {{ $brand }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
