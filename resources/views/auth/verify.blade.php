<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.auth.verify_page_title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        .logo {
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2563eb;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .logo p {
            color: #6b7280;
            font-size: 14px;
        }
        .verify-icon {
            font-size: 48px;
            color: #2563eb;
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        .btn-outline-primary {
            border: 2px solid #2563eb;
            color: #2563eb;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: transparent;
        }
        .btn-outline-primary:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        .auth-links a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .success-message {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .info-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .email-icon {
            font-size: 24px;
            color: #2563eb;
            margin-right: 10px;
        }
        .auth-card .locale-switcher .dropdown-toggle {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 0.45rem 0.85rem;
            font-weight: 500;
            color: #374151;
            background: #fff;
        }
        .auth-card .locale-switcher .dropdown-toggle:hover,
        .auth-card .locale-switcher .dropdown-toggle:focus,
        .auth-card .locale-switcher .dropdown-toggle.show {
            border-color: #1C575F;
            color: #1C575F;
            background: #f8fafc;
        }
        .auth-card .locale-switcher .dropdown-menu {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-top: 0.35rem;
        }
        .auth-card .locale-switcher .dropdown-item.active {
            background: rgba(28, 87, 95, 0.08);
            color: #1C575F;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="d-flex justify-content-end mb-2">
                @include('partials.locale-switcher')
            </div>
            <div class="logo">
                <h1>VujaDe</h1>
                <p>{{ __('portal.auth.verify_tagline') }}</p>
            </div>

            <div class="verify-icon">
                <i class="fas fa-envelope-open"></i>
            </div>

            @if (session('resent'))
                <div class="success-message">
                    <i class="fas fa-check-circle me-2"></i>{{ __('portal.auth.verify_resent_message') }}
                </div>
            @endif

            <p class="info-text">
                {{ __('portal.auth.verify_intro') }}
            </p>

            <div class="d-flex align-items-center justify-content-center mb-4">
                <i class="email-icon fas fa-envelope"></i>
                <span style="color: #374151; font-weight: 500;">{{ __('portal.auth.verify_check_inbox') }}</span>
            </div>

            <p class="info-text">
                {{ __('portal.auth.verify_resend_hint') }}
            </p>

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-paper-plane me-2"></i>{{ __('portal.auth.resend_verification') }}
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('portal.auth.sign_out') }}
                </a>
            </div>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
