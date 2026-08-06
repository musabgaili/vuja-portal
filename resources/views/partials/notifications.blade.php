@php
    $notifSvc = app(\App\Services\NotificationService::class);
    $notifItems = $notifSvc->feed(auth()->user());
    $notifUnread = $notifSvc->unreadCount(auth()->user());
    $notifIndexRoute = auth()->user()->isInternal() ? 'notifications.index' : 'client.notifications.index';
    $notifSeenRoute = auth()->user()->isInternal() ? 'notifications.seen' : 'client.notifications.seen';
@endphp
<div class="dropdown notif-bell">
    <button type="button" id="notifBell" class="btn position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
            title="{{ __('portal.notif.title') }}" aria-label="{{ __('portal.notif.title') }}{{ $notifUnread > 0 ? ' ('.$notifUnread.')' : '' }}"
            style="background:transparent; border:none; color:var(--gray-600); font-size:1.1rem; padding:.35rem .5rem;">
        <i class="fas fa-bell" aria-hidden="true"></i>
        <span id="notifBadge" class="badge rounded-pill bg-danger position-absolute" aria-hidden="true"
              style="top:2px; inset-inline-end:0; font-size:.6rem; {{ $notifUnread > 0 ? '' : 'display:none;' }}">{{ $notifUnread > 9 ? '9+' : $notifUnread }}</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow" style="width:330px; max-height:420px; overflow:auto;">
        <h6 class="dropdown-header">{{ __('portal.notif.title') }}</h6>
        @forelse($notifItems as $it)
            <a class="dropdown-item d-flex gap-2 align-items-start py-2" href="{{ $it['url'] }}" style="white-space:normal;">
                <i class="fas {{ $it['icon'] }}" style="color:var(--primary-color); margin-top:3px;"></i>
                <span>
                    <span style="font-size:.85rem;">{{ $it['text'] }}</span>
                    <br><small class="text-muted">{{ $it['ago'] }}</small>
                </span>
            </a>
        @empty
            <span class="dropdown-item text-muted">{{ __('portal.notif.empty') }}</span>
        @endforelse
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center fw-bold" href="{{ route($notifIndexRoute) }}" style="color:var(--primary-color);">{{ __('portal.notif.view_all') }}</a>
    </div>
</div>
<script>
(function () {
    var bell = document.getElementById('notifBell');
    if (!bell) return;
    bell.addEventListener('click', function () {
        var badge = document.getElementById('notifBadge');
        if (!badge || badge.style.display === 'none') return;
        fetch("{{ route($notifSeenRoute) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        }).then(function () { badge.style.display = 'none'; }).catch(function () {});
    });
})();
</script>
