{{-- Sahem-style theming: Arabic-capable fonts + FOUC-free light/dark init.
     Include this near the top of every layout's <head>. --}}
{{-- Brand favicon — your logo (public/images/vd-favicon.png) if uploaded, else the SVG mark. --}}
@if(file_exists(public_path('images/vd-favicon.png')))
    <link rel="icon" type="image/png" href="{{ asset('images/vd-favicon.png') }}">
@else
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
<script>
(function () {
    try {
        var t = localStorage.getItem('vuja-theme');
        if (t !== 'light' && t !== 'dark') {
            t = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', t);
    } catch (e) {}

    window.toggleTheme = function () {
        var el = document.documentElement;
        var next = el.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        el.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('vuja-theme', next); } catch (e) {}
        document.querySelectorAll('[data-theme-icon]').forEach(function (i) {
            i.className = (next === 'dark' ? 'fas fa-sun' : 'fas fa-moon');
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        document.querySelectorAll('[data-theme-icon]').forEach(function (i) {
            i.className = (dark ? 'fas fa-sun' : 'fas fa-moon');
        });

        // Keep the sidebar scrolled where it was across full-page navigations.
        var nav = document.querySelector('.sidebar-nav') || document.querySelector('.sidebar');
        if (nav) {
            try {
                var s = sessionStorage.getItem('vuja-sidebar-scroll');
                if (s !== null) nav.scrollTop = parseInt(s, 10) || 0;
            } catch (e) {}
            nav.addEventListener('scroll', function () {
                try { sessionStorage.setItem('vuja-sidebar-scroll', nav.scrollTop); } catch (e) {}
            }, { passive: true });
            // Save immediately on nav click so the next page restores precisely.
            nav.addEventListener('click', function () {
                try { sessionStorage.setItem('vuja-sidebar-scroll', nav.scrollTop); } catch (e) {}
            }, true);
        }
    });
})();
</script>
