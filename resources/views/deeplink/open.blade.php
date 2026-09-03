<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open in VujaDe</title>
    <meta http-equiv="refresh" content="0;url={{ $deepLink }}">
    <style>
        body { font-family: system-ui, sans-serif; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; background: #0f172a; color: #e2e8f0; }
        .card { text-align: center; padding: 2rem; max-width: 28rem; }
        a.btn { display: inline-block; margin-top: 1.25rem; padding: .75rem 1.25rem; background: #38bdf8; color: #0f172a; font-weight: 600; text-decoration: none; border-radius: .5rem; }
        p { opacity: .8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Opening VujaDe</h1>
        <p>If the app does not open automatically, tap the button below.</p>
        <a class="btn" href="{{ $deepLink }}">Open in app</a>
    </div>
    <script>window.location.replace(@json($deepLink));</script>
</body>
</html>
