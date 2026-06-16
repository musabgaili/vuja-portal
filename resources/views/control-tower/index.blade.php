@extends('layouts.internal-dashboard')
@section('title', __('portal.control_tower.title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-tower-observation"></i> {{ __('portal.control_tower.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.control_tower.subtitle') }}</p>
</div>

@php
    $lights = [
        'green'  => ['label' => __('portal.control_tower.healthy'),  'color' => '#16a34a', 'icon' => 'fa-circle-check'],
        'yellow' => ['label' => __('portal.control_tower.at_risk'),  'color' => '#d97706', 'icon' => 'fa-triangle-exclamation'],
        'red'    => ['label' => __('portal.control_tower.critical'), 'color' => '#dc2626', 'icon' => 'fa-circle-exclamation'],
    ];
@endphp

{{-- Traffic-light summary --}}
<div class="row g-3 mb-3">
    @foreach(['red','yellow','green'] as $key)
        <div class="col-md-4">
            <div class="card" style="border-inline-start:4px solid {{ $lights[$key]['color'] }};">
                <div class="card-content d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted" style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em;">{{ $lights[$key]['label'] }}</div>
                        <div style="font-size:2rem; font-weight:800;">{{ $counts[$key] }}</div>
                    </div>
                    <i class="fas {{ $lights[$key]['icon'] }}" style="font-size:2rem; color:{{ $lights[$key]['color'] }};"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Projects by health --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.control_tower.projects') }}</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th></th><th>{{ __('portal.control_tower.project') }}</th><th>{{ __('portal.control_tower.manager') }}</th><th>{{ __('portal.control_tower.flags') }}</th></tr></thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:{{ $lights[$row['health']]['color'] }};" title="{{ $lights[$row['health']]['label'] }}"></span></td>
                            <td>
                                <a href="{{ route('projects.manager.show', $row['project']) }}" style="font-weight:600;">{{ $row['project']->title }}</a>
                                <div class="text-muted" style="font-size:.75rem;">{{ $row['project']->getStatusLabel() }}</div>
                            </td>
                            <td>{{ $row['project']->projectManager->name ?? '—' }}</td>
                            <td>
                                @forelse($row['flags'] as $f)
                                    <span class="badge {{ $f['severity'] === 'red' ? 'bg-danger' : 'bg-warning' }}" style="margin:1px;">{{ $f['label'] }}</span>
                                @empty
                                    <span class="badge bg-success">{{ __('portal.control_tower.healthy') }}</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">{{ __('portal.control_tower.all_healthy') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Risk alert feed --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-bell"></i> {{ __('portal.control_tower.alerts') }}</span></div>
            <div class="card-content">
                @forelse($alerts as $a)
                    <div class="d-flex align-items-start gap-2 py-2" style="border-bottom:1px solid var(--gray-200);">
                        <i class="fas {{ $a['severity'] === 'red' ? 'fa-circle-exclamation' : 'fa-triangle-exclamation' }}"
                           style="color:{{ $a['severity'] === 'red' ? '#dc2626' : '#d97706' }}; margin-top:3px;"></i>
                        <div>
                            <div style="font-size:.85rem;">{{ $a['label'] }}</div>
                            <a href="{{ route('projects.manager.show', $a['project']) }}" class="text-muted" style="font-size:.75rem;">{{ $a['project']->title }}</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('portal.control_tower.no_alerts') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
