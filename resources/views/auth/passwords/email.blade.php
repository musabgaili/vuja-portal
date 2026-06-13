<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.auth.reset_password_page_title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0F969C 0%, #0C7075 100%);
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
            max-width: 400px;
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
        .reset-icon {
            font-size: 48px;
            color: #2563eb;
            margin-bottom: 20px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
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
            border-color: #2C3F43;
            color: #2C3F43;
            background: #f8fafc;
        }
        .auth-card .locale-switcher .dropdown-menu {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-top: 0.35rem;
        }
        .auth-card .locale-switcher .dropdown-item.active {
            background: rgba(28, 87, 95, 0.08);
            color: #2C3F43;
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
                <p>{{ __('portal.auth.reset_tagline_request') }}</p>
            </div>

            <div class="reset-icon">
                <i class="fas fa-key"></i>
            </div>

            @if (session('status'))
                <div class="success-message">
                    <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif

            <p class="info-text">
                {{ __('portal.auth.reset_email_instructions') }}
            </p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <div class="form-floating">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="{{ __('portal.email_address') }}" required autofocus>
                    <label for="email">
                        <i class="fas fa-envelope me-2"></i>{{ __('portal.email_address') }}
                    </label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>{{ __('portal.auth.send_reset_link') }}
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('portal.auth.back_to_login') }}
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
