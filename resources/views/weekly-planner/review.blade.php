@extends('layouts.internal-dashboard')
@section('title', 'Weekly Plans Review')

@section('content')
@php
    $tvTotal = max(1, $timeValue['projects'] + $timeValue['development'] + $timeValue['presale']);
    $pPct = round($timeValue['projects'] / $tvTotal * 100);
    $dPct = round($timeValue['development'] / $tvTotal * 100);
    $sPct = 100 - $pPct - $dPct;
@endphp
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-clipboard-check"></i> Weekly Plans — Review</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">Week of {{ $weekStart->format('D, M j, Y') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('weekly-planner.presence') }}" class="btn btn-outline-primary"><i class="fas fa-map-marker-alt"></i> Team Presence</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title">Pending Approvals ({{ $pending->count() }})</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>Employee</th><th>Projects</th><th>Dev</th><th>Pre-sale</th><th>Total</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    @forelse($pending as $p)
                        <tr>
                            <td>{{ $p->user->name }}</td>
                            <td>{{ $p->hours_projects }}h</td><td>{{ $p->hours_development }}h</td><td>{{ $p->hours_presale }}h</td>
                            <td><strong>{{ $p->totalHours() }}h</strong></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('weekly-planner.approve', $p) }}" class="d-inline">@csrf
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
                                </form>
                                <form method="POST" action="{{ route('weekly-planner.reject', $p) }}" class="d-inline">@csrf
                                    <button class="btn btn-outline-primary btn-sm" title="Send back"><i class="fas fa-rotate-left"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center py-3">No plans awaiting approval. 🎉</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><span class="card-title">All Plans This Week</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>Employee</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($plans as $p)
                        <tr><td>{{ $p->user->name }}</td><td>{{ $p->totalHours() }}h</td>
                            <td><span class="badge bg-{{ $p->statusColor() }}">{{ ucfirst($p->status) }}</span></td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center py-3">No plans submitted yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Time-Value donut --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><span class="card-title">Time-Value Split</span></div>
            <div class="card-content text-center">
                <div style="width:170px; height:170px; border-radius:50%; margin:0 auto;
                            background: conic-gradient(var(--primary-color) 0 {{ $pPct }}%, var(--primary-bright) {{ $pPct }}% {{ $pPct + $dPct }}%, var(--primary-accent) {{ $pPct + $dPct }}% 100%);
                            display:flex; align-items:center; justify-content:center;">
                    <div style="width:108px; height:108px; border-radius:50%; background:var(--bg-primary); display:flex; align-items:center; justify-content:center; flex-direction:column;">
                        <strong style="font-size:1.1rem;">{{ $tvTotal }}h</strong>
                        <small class="text-muted">total</small>
                    </div>
                </div>
                <div class="mt-3 text-start" style="font-size:.85rem;">
                    <div class="d-flex justify-content-between"><span><i class="fas fa-square" style="color:var(--primary-color)"></i> Projects</span><span>{{ $pPct }}%</span></div>
                    <div class="d-flex justify-content-between"><span><i class="fas fa-square" style="color:var(--primary-bright)"></i> Development</span><span>{{ $dPct }}%</span></div>
                    <div class="d-flex justify-content-between"><span><i class="fas fa-square" style="color:var(--primary-accent)"></i> Pre-sale</span><span>{{ $sPct }}%</span></div>
                </div>
                <p class="text-muted mt-2" style="font-size:.75rem;">Watch non-billable (Dev + Pre-sale) share.</p>
            </div>
        </div>
    </div>
</div>
@endsection
