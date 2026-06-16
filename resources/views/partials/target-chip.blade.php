{{-- Performance-target progress chip for the internal top bar (beside the XP chip). --}}
@php
    $tgtUser = auth()->user();
    $tgtShow = $tgtUser && $tgtUser->holdsTargets();
    $tgtOverall = $tgtShow ? app(\App\Services\Targets\ActualsService::class)->overallAttainment($tgtUser, now()->startOfMonth()) : null;
@endphp
@if($tgtShow)
    @php $tgtRag = $tgtOverall === null ? 'none' : ($tgtOverall >= 100 ? 'green' : ($tgtOverall >= 70 ? 'amber' : 'red')); @endphp
    <a href="{{ route('targets.my') }}" class="tgt-chip tgt-{{ $tgtRag }}" title="{{ __('targets.chip_title') }}">
        <span class="tgt-label"><i class="fas fa-bullseye"></i> {{ __('targets.chip') }}</span>
        @if($tgtOverall === null)
            <span class="tgt-pct" style="font-weight:600;">{{ __('targets.chip_none') }}</span>
        @else
            <span class="tgt-track"><span class="tgt-fill" style="width: {{ min(100, $tgtOverall) }}%"></span></span>
            <span class="tgt-pct">{{ (int) $tgtOverall }}%</span>
        @endif
    </a>
    <style>
        .tgt-chip{ display:inline-flex; align-items:center; gap:.5rem; padding:.3rem .6rem; border-radius:999px;
            background:var(--gray-100); border:1px solid var(--gray-200); text-decoration:none; color:var(--text-color); font-size:.78rem; }
        .tgt-chip .tgt-label{ font-weight:700; white-space:nowrap; }
        .tgt-chip .tgt-track{ width:70px; height:7px; border-radius:999px; background:var(--gray-300); overflow:hidden; }
        .tgt-chip .tgt-fill{ display:block; height:100%; border-radius:999px; }
        .tgt-chip .tgt-pct{ font-weight:800; }
        .tgt-green .tgt-fill{ background:var(--success-color); } .tgt-green .tgt-pct{ color:var(--success-color); }
        .tgt-amber .tgt-fill{ background:var(--warning-color); } .tgt-amber .tgt-pct{ color:var(--warning-color); }
        .tgt-red   .tgt-fill{ background:var(--danger-color); }  .tgt-red   .tgt-pct{ color:var(--danger-color); }
        .tgt-none .tgt-pct{ color:var(--gray-500); }
        @media (max-width: 720px){ .tgt-chip .tgt-track{ display:none; } }
    </style>
@endif
