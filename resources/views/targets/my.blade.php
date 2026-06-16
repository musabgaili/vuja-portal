@extends('layouts.internal-dashboard')
@section('title', __('targets.my_title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-bullseye"></i> {{ __('targets.my_title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.my_subtitle', ['month' => $month->translatedFormat('F Y')]) }}</p>
    </div>
    @if($overall !== null)
        <div class="tgt-hero-stat">
            <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.85;">{{ __('targets.overall') }}</div>
            <div style="font-size:1.8rem; font-weight:800;">{{ (int) $overall }}%</div>
        </div>
    @endif
</div>

@if($summary->isEmpty())
    <div class="card"><div class="card-content text-center py-5">
        <i class="fas fa-bullseye fa-3x mb-3" style="color:var(--gray-400);"></i>
        <h4 style="color:var(--text-color);">{{ __('targets.none_title') }}</h4>
        <p class="text-muted">{{ __('targets.none_body') }}</p>
    </div></div>
@else
    <div class="row g-3">
        @foreach($summary as $row)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-0">{{ $row->metric->localizedName() }}</h5>
                                <span class="text-muted" style="font-size:.8rem;">
                                    {{ __('targets.progress_label', ['actual' => rtrim(rtrim(number_format($row->actual, 2), '0'), '.'), 'target' => rtrim(rtrim(number_format($row->target_value, 2), '0'), '.')]) }}
                                </span>
                            </div>
                            <span class="badge bg-{{ $row->rag === 'green' ? 'success' : ($row->rag === 'amber' ? 'warning' : 'danger') }}">{{ (int) $row->attainment }}%</span>
                        </div>
                        <div class="tgt-bar"><div class="tgt-bar__fill tgt-{{ $row->rag }}" style="width: {{ min(100, $row->attainment) }}%;"></div></div>
                        @if($row->attainment >= 100)
                            <div class="text-success mt-2" style="font-size:.8rem;"><i class="fas fa-circle-check"></i> {{ __('targets.hit_bonus') }}</div>
                        @endif

                        @if(isset($trends[$row->metric->id]))
                            <hr>
                            <div class="text-muted mb-1" style="font-size:.78rem;">{{ __('targets.trend_6mo') }}</div>
                            @include('partials.target-trend', ['points' => $trends[$row->metric->id]])
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('styles')
<style>
.tgt-hero-stat{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); border-radius:var(--radius-lg); padding:.5rem 1.1rem; text-align:center; }
.tgt-bar{ height:10px; border-radius:999px; background:var(--gray-200); overflow:hidden; }
.tgt-bar__fill{ height:100%; border-radius:999px; }
.tgt-bar__fill.tgt-green{ background:var(--success-color); }
.tgt-bar__fill.tgt-amber{ background:var(--warning-color); }
.tgt-bar__fill.tgt-red{ background:var(--danger-color); }
</style>
@endpush
