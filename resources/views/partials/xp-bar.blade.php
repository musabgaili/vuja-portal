{{-- Impact Points XP chip for the internal top bar. --}}
@php
    $ipUser = auth()->user();
    $ipLevel = $ipUser->engagementLevel();
    $ipProgress = $ipUser->engagementProgress();
@endphp
<a href="{{ route('engagement.index') }}" class="xp-chip" title="{{ $ipLevel['name'] }} — {{ number_format($ipUser->impact_points) }} IP">
    <span class="xp-level"><i class="fas fa-bolt"></i> {{ $ipLevel['name'] }}</span>
    <span class="xp-track"><span class="xp-fill" style="width: {{ $ipProgress }}%"></span></span>
    <span class="xp-pts">{{ number_format($ipUser->impact_points) }} IP</span>
</a>
