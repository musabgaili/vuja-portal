{{-- Persistent "activate your account" banner for any signed-in, unverified user.
     Stays on every page until the email is verified; resend is rate-limited
     (server throttle + a 60s client-side cooldown that survives page reloads). --}}
@auth
    @unless(auth()->user()->hasVerifiedEmail())
        @if(session('resent'))
            <div class="verify-banner verify-banner--ok" role="status">
                <i class="fas fa-circle-check"></i> {{ __('portal.verify_banner.sent') }}
            </div>
        @endif
        <div class="verify-banner" role="alert">
            <div class="verify-banner__text">
                <i class="fas fa-triangle-exclamation"></i>
                <span>{{ __('portal.verify_banner.message') }}</span>
            </div>
            <form method="POST" action="{{ route('verification.resend') }}" id="verifyResendForm" class="verify-banner__action">
                @csrf
                <button type="submit" id="verifyResendBtn" class="btn btn-sm">
                    <i class="fas fa-paper-plane"></i> <span id="verifyResendLabel">{{ __('portal.verify_banner.resend') }}</span>
                </button>
            </form>
        </div>
        <style>
            .verify-banner { background:#fef3c7; color:#92400e; border-bottom:2px solid #f59e0b;
                padding:.6rem 1.25rem; display:flex; align-items:center; justify-content:space-between;
                gap:1rem; flex-wrap:wrap; font-size:.9rem; }
            .verify-banner--ok { background:#d1fae5; color:#065f46; border-bottom-color:#10b981; }
            .verify-banner__text { display:flex; align-items:center; gap:.5rem; }
            .verify-banner .btn { background:#92400e; color:#fff; border:none; white-space:nowrap; font-weight:600; }
            .verify-banner .btn:hover { background:#7c360b; color:#fff; }
            .verify-banner .btn:disabled { opacity:.55; cursor:not-allowed; }
            [data-bs-theme="dark"] .verify-banner { background:#3a2e0a; color:#fde68a; }
            [data-bs-theme="dark"] .verify-banner--ok { background:#0c2f22; color:#a7f3d0; }
        </style>
        <script>
            (function () {
                var KEY = 'vd-verify-resend-until', COOLDOWN = 60; // seconds
                var btn = document.getElementById('verifyResendBtn');
                var label = document.getElementById('verifyResendLabel');
                var form = document.getElementById('verifyResendForm');
                if (!btn || !label || !form) return;
                var base = label.textContent;
                function tick() {
                    var until = parseInt(localStorage.getItem(KEY) || '0', 10);
                    var left = Math.ceil((until - Date.now()) / 1000);
                    if (left > 0) { btn.disabled = true; label.textContent = base + ' (' + left + 's)'; setTimeout(tick, 1000); }
                    else { btn.disabled = false; label.textContent = base; }
                }
                // Start the cooldown the moment they submit (survives the page reload).
                form.addEventListener('submit', function () {
                    localStorage.setItem(KEY, (Date.now() + COOLDOWN * 1000).toString());
                });
                tick();
            })();
        </script>
    @endunless
@endauth
