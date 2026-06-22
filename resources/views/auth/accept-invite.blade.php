<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.invite.page_title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --vd-teal: #0F969C; --vd-teal-bright: #0BABB5; --vd-slate: #2C3F43; --vd-deep: #072E33; }
        body {
            background: var(--vd-deep);
            background-image:
                radial-gradient(900px circle at 12% 18%, rgba(15,150,156,.35), transparent 45%),
                radial-gradient(800px circle at 88% 88%, rgba(11,171,181,.22), transparent 45%),
                linear-gradient(135deg, var(--vd-slate) 0%, var(--vd-deep) 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 24px;
        }
        .invite-card { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; box-shadow: 0 18px 48px rgba(0,0,0,.35); overflow: hidden; }
        .invite-head { background: linear-gradient(135deg, var(--vd-teal) 0%, var(--vd-deep) 100%); color: #fff; padding: 28px; text-align: center; }
        .invite-head h1 { font-size: 1.25rem; margin: 0 0 6px; }
        .invite-head p { margin: 0; opacity: .85; font-size: .9rem; }
        .invite-body { padding: 28px; }
        .btn-teal { background: var(--vd-teal); border: none; color: #fff; font-weight: 600; }
        .btn-teal:hover { background: var(--vd-teal-bright); color: #fff; }
    </style>
</head>
<body>
    <div class="invite-card">
        <div class="invite-head">
            <div style="font-size:2rem;margin-bottom:6px;"><i class="fas fa-user-check"></i></div>
            <h1>{{ __('portal.invite.welcome_heading') }}</h1>
            <p>{{ $invitee->name }} — {{ $invitee->email }}</p>
        </div>
        <div class="invite-body">
            <p class="text-muted" style="font-size:.92rem;">{{ __('portal.invite.set_password_intro') }}</p>

            @if($errors->any())
            <div class="alert alert-danger" style="font-size:.85rem;">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ $actionUrl }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('portal.invite.password') }}</label>
                    <input type="password" name="password" class="form-control" required autofocus autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('portal.invite.password_confirm') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-teal w-100">
                    <i class="fas fa-check"></i> {{ __('portal.invite.activate') }}
                </button>
            </form>
        </div>
    </div>
</body>
</html>
