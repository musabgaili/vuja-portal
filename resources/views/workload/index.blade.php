@extends('layouts.internal-dashboard')
@section('title', __('portal.workload.title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-fire"></i> {{ __('portal.workload.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.workload.subtitle') }}</p>
</div>

@php
    // teal heat cell: opacity scales with value/max
    $heat = function ($v, $m) {
        $r = $m > 0 ? $v / $m : 0;
        $op = $v === 0 ? 0.0 : 0.15 + 0.85 * $r;
        return "background: rgba(12,112,117," . round($op, 2) . "); color:" . ($r > 0.55 ? '#fff' : 'var(--gray-800)') . ";";
    };
    $capColors = ['idle'=>'#94a3b8','light'=>'#16a34a','moderate'=>'#0F969C','busy'=>'#d97706','overloaded'=>'#dc2626'];
@endphp

<div class="card">
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:680px; text-align:center;">
            <thead>
                <tr>
                    <th style="text-align:start;">{{ __('portal.workload.employee') }}</th>
                    <th>{{ __('portal.workload.projects') }}</th>
                    <th>{{ __('portal.workload.tasks') }}</th>
                    <th>{{ __('portal.workload.requests') }}</th>
                    <th>{{ __('portal.workload.load') }}</th>
                    <th>{{ __('portal.workload.capacity') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td style="text-align:start; font-weight:600;">
                            <a href="{{ route('workload.show', $row['user']) }}" style="color:var(--primary-color); text-decoration:none;">{{ $row['user']->name }} <i class="fas fa-up-right-from-square" style="font-size:.7rem;"></i></a>
                        </td>
                        <td style="{{ $heat($row['projects'], $maxProjects) }} border-radius:6px;">{{ $row['projects'] }}</td>
                        <td style="{{ $heat($row['tasks'], $maxTasks) }} border-radius:6px;">{{ $row['tasks'] }}</td>
                        <td style="{{ $heat($row['requests'], $maxRequests) }} border-radius:6px;">{{ $row['requests'] }}</td>
                        <td style="{{ $heat($row['load'], $max) }} border-radius:6px; font-weight:700;">{{ $row['load'] }}</td>
                        <td>
                            <span class="badge" style="background: {{ $capColors[$row['capacity']] }}1f; color: {{ $capColors[$row['capacity']] }};">
                                {{ __('portal.workload.cap.'.$row['capacity']) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted py-3">{{ __('portal.workload.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted mt-2" style="font-size:.8rem;">{{ __('portal.workload.legend') }}</p>
@endsection
