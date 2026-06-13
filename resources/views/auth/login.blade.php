<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.login_page_title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2C3F43 0%, #1d2a2d 100%);
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
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(28, 87, 95, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(28, 87, 95, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2C3F43;
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .logo p {
            color: #64748b;
            font-size: 14px;
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
            border-color: #2C3F43;
            box-shadow: 0 0 0 0.2rem rgba(28, 87, 95, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2C3F43 0%, #1d2a2d 100%);
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
            box-shadow: 0 10px 20px rgba(28, 87, 95, 0.4);
            background: linear-gradient(135deg, #0BABB5 0%, #2C3F43 100%);
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
            margin-top: 20px;
        }
        .auth-links a {
            color: #2C3F43;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-links a:hover {
            text-decoration: underline;
            color: #0BABB5;
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
                <h1>VujaDe</h1>
                <p>{{ __('portal.welcome_back') }}</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
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

                <div class="form-floating">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="{{ __('portal.password') }}" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>{{ __('portal.password') }}
                    </label>
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
