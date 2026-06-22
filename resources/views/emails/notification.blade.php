@php $rtl = ($lang ?? 'en') === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <tr><td style="background:#1B565E;padding:18px 28px;color:#fff;font-size:18px;font-weight:700;">
          {{ config('app.name', 'VujaDe') }}
        </td></tr>
        <tr><td style="padding:28px;">
          <h1 style="margin:0 0 12px;font-size:18px;color:#1B565E;">{{ $heading }}</h1>
          <p style="margin:0 0 20px;font-size:15px;line-height:1.6;white-space:pre-line;">{{ $body }}</p>
          @if($actionUrl)
            <p style="margin:0 0 8px;">
              <a href="{{ $actionUrl }}" style="display:inline-block;background:#1B565E;color:#fff;text-decoration:none;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;">
                {{ $actionText ?: __('portal.notif_prefs.email_cta') }}
              </a>
            </p>
          @endif
        </td></tr>
        <tr><td style="padding:16px 28px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
          {{ __('portal.notif_prefs.email_footer') }}
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
