<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.login_page_title') }}</title>
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        /* faint dotted brand texture over the gradient */
        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none; opacity: .5;
            background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(7, 46, 51, 0.45);
            padding: 40px;
            width: 100%;
            max-width: 410px;
            border: 1px solid rgba(28, 87, 95, 0.1);
        }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo .vd-logo-img {
            display: block;
            width: 190px;
            max-width: 70%;
            height: auto;
            margin: 0 auto 6px;
        }
        .logo p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
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
            border-color: var(--vd-teal);
            box-shadow: 0 0 0 0.2rem rgba(15, 150, 156, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--vd-teal-bright) 0%, var(--vd-teal) 100%);
            border: none;
            border-radius: 12px;
            padding: 13px 24px;
            font-weight: 700;
            font-size: 16px;
            width: 100%;
            margin-bottom: 18px;
            transition: all 0.25s ease;
        }
        .btn-primary:hover, .btn-primary:focus {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 150, 156, 0.45);
            background: linear-gradient(135deg, var(--vd-teal) 0%, var(--vd-slate) 100%);
        }
        .social-login {
            margin: 20px 0;
        }
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .social-btn:hover {
            border-color: #2C3F43;
            color: #2C3F43;
            transform: translateY(-1px);
        }
        .social-btn i {
            margin-right: 10px;
            font-size: 18px;
        }
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }
        .divider span {
            background: rgba(255, 255, 255, 0.98);
            padding: 0 15px;
            color: #6b7280;
            font-size: 14px;
        }
        .auth-links {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #eef2f2;
            font-size: 14.5px;
            line-height: 2;
        }
        .auth-links a {
            color: var(--vd-teal);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-links a:hover {
            text-decoration: underline;
            color: var(--vd-slate);
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .form-check-input {
            margin-right: 8px;
        }
        .form-check-label {
            color: #6b7280;
            font-size: 14px;
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
                <img src="{{ asset('images/vd-logo-light.png') }}?v={{ @filemtime(public_path('images/vd-logo-light.png')) }}"
                     alt="Vujà Dé Innovation" class="vd-logo-img">
                <p>{{ __('portal.welcome_back') }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @php
                $demoLogin = app()->environment(['local', 'testing']);
                $loginEmail = old('email', $demoLogin ? 'manager@vujade.com' : '');
                $loginPassword = $demoLogin ? '12345678' : '';
            @endphp
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>{{ __('portal.email_address') }}
                    </label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ $loginEmail }}"
                           placeholder="{{ __('portal.email_address') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>{{ __('portal.password') }}
                    </label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" value="{{ $loginPassword }}"
                           placeholder="{{ __('portal.password') }}" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-me">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('portal.remember_me') }}</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>{{ __('portal.sign_in') }}
                </button>
            </form>

            {{-- Social login buttons hidden for now --}}
            {{-- <div class="divider">
                <span>or continue with</span>
            </div>

            <div class="social-login">
                <a href="{{ route('social.redirect', 'google') }}" class="social-btn">
                    <i class="fab fa-google"></i>
                    Continue with Google
                </a>
                <a href="{{ route('social.redirect', 'facebook') }}" class="social-btn">
                    <i class="fab fa-facebook-f"></i>
                    Continue with Facebook
                </a>
                <a href="{{ route('social.redirect', 'linkedin') }}" class="social-btn">
                    <i class="fab fa-linkedin-in"></i>
                    Continue with LinkedIn
                </a>
            </div> --}}

            <div class="auth-links">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">{{ __('portal.forgot_password') }}</a>
                @endif
                <br>
                <span style="color: #6b7280;">{{ __('portal.no_account') }}</span>
                <a href="{{ route('register') }}">{{ __('portal.sign_up') }}</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
