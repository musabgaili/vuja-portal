<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('errors.title_500') }} - VujaDe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-4">
        <div class="card border-0 shadow-sm text-center" style="max-width: 520px;">
            <div class="card-body p-5">
                <h1 class="display-4 fw-bold text-danger">500</h1>
                <h2 class="h4 mb-3">{{ __('errors.title_500') }}</h2>
                <p class="text-muted">{{ __('errors.message_500') }}</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">{{ __('errors.back_home') }}</a>
            </div>
        </div>
    </main>
</body>
</html>
