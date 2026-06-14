{{-- Impact Points XP chip for the internal top bar. --}}
@php
    $ipUser = auth()->user();
    $ipLevel = $ipUser->engagementLevel();
    $ipProgress = $ipUser->engagementProgress();
@endphp
@php $ipLevelName = __('portal.engagement.level.'.$ipLevel['index']); @endphp
<a href="{{ route('engagement.index') }}" class="xp-chip" title="{{ $ipLevelName }} — {{ number_format($ipUser->impact_points) }} {{ __('portal.engagement.ip') }}">
    <span class="xp-level"><i class="fas fa-bolt"></i> {{ $ipLevelName }}</span>
    <span class="xp-track"><span class="xp-fill" style="width: {{ $ipProgress }}%"></span></span>
    <span class="xp-pts">{{ number_format($ipUser->impact_points) }} {{ __('portal.engagement.ip') }}</span>
</a>
