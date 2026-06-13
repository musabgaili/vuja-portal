{{-- Real-time Impact Points toasts (flashed by EngagementService::award). --}}
@if(session('ip_toasts'))
<div class="ip-toast-wrap" id="ipToastWrap">
    @foreach(session('ip_toasts') as $t)
        @php $pts = (int) ($t['points'] ?? 0); $pos = $pts >= 0; @endphp
        <div class="ip-toast {{ $pos ? 'pos' : 'neg' }}">
            <i class="fas {{ $pos ? 'fa-bolt' : 'fa-triangle-exclamation' }}"></i>
            <span>{{ $pos ? '+' : '' }}{{ $pts }} IP</span>
            <small>{{ str_replace('_', ' ', $t['action'] ?? '') }}</small>
        </div>
    @endforeach
</div>
<script>
    setTimeout(function () {
        var w = document.getElementById('ipToastWrap');
        if (w) { w.style.transition = 'opacity .5s'; w.style.opacity = '0'; setTimeout(function () { w.remove(); }, 600); }
    }, 4200);
</script>
@endif
