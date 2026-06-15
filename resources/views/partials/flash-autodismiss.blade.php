{{-- Auto-dismiss transient success flash messages.
     A success flash (e.g. "Draft saved.") fades out on its own after a few
     seconds so the user does not have to click the X. We deliberately scope
     this to .alert-success because that is what session flash success uses;
     info/warning/danger alerts and any state-bearing success box marked with
     data-persist are left alone. Loaded after Bootstrap JS. --}}
<script>
(function () {
    var DISMISS_AFTER = 4000; // ms
    function autoDismiss() {
        var alerts = document.querySelectorAll('.alert-success:not([data-persist])');
        alerts.forEach(function (el) {
            window.setTimeout(function () {
                if (!el || !el.parentNode) { return; }
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    el.classList.add('fade', 'show'); // ensure the closing transition runs
                    bootstrap.Alert.getOrCreateInstance(el).close();
                } else {
                    el.style.transition = 'opacity .4s ease';
                    el.style.opacity = '0';
                    window.setTimeout(function () { if (el.parentNode) { el.remove(); } }, 450);
                }
            }, DISMISS_AFTER);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoDismiss);
    } else {
        autoDismiss();
    }
})();
</script>
