@extends('layouts.internal-dashboard')
@section('title', __('targets.cap.my_title'))

@section('content')
@php
    $lineByCat = $allocation->lines->keyBy('activity_category_id');
    $utilRag = $utilization >= $utilizationTarget ? 'success' : ($utilization >= $utilizationTarget * 0.8 ? 'warning' : 'danger');
@endphp
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-gauge-high"></i> {{ __('targets.cap.my_title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.cap.my_subtitle', ['month' => $month->translatedFormat('F Y')]) }}</p>
    </div>
    <div class="tgt-hero-stat">
        <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.85;">{{ __('targets.cap.utilization') }}</div>
        <div style="font-size:1.8rem; font-weight:800;">{{ (int) $utilization }}%</div>
        <div style="font-size:.7rem; opacity:.8;">{{ __('targets.cap.target') }} {{ (int) $utilizationTarget }}%</div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="row g-3">
    {{-- Weekly allocation form --}}
    <div class="col-lg-5">
        {{-- My committed working hours (>= the manager-set floor) --}}
        <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-business-time"></i> {{ __('targets.cap.my_hours') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('capacity.hours') }}" class="row g-2 align-items-end">
                @csrf @method('PUT')
                <div class="col-7">
                    <label class="form-label" style="font-size:.82rem;">{{ __('targets.cap.weekly_hours') }}</label>
                    <input type="number" step="0.5" min="{{ rtrim(rtrim(number_format($member->min_weekly_hours, 1), '0'), '.') }}" max="168"
                        name="weekly_capacity_hours" class="form-control"
                        value="{{ rtrim(rtrim(number_format($member->weekly_capacity_hours, 1), '0'), '.') }}" required>
                </div>
                <div class="col-5">
                    <button class="btn btn-primary w-100">{{ __('targets.cap.save_hours') }}</button>
                </div>
            </form>
            <small class="text-muted d-block mt-2">{{ __('targets.cap.min_floor_note', ['hours' => rtrim(rtrim(number_format($member->min_weekly_hours, 1), '0'), '.')]) }}</small>
        </div></div>

        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-calendar-week"></i> {{ __('targets.cap.this_week') }}</span></div>
        <div class="card-content">
            <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
                <span class="text-muted">{{ $week->translatedFormat('M d') }} – {{ $week->copy()->addDays(6)->translatedFormat('M d') }}</span>
                <span>{{ __('targets.cap.available') }}: <strong>{{ rtrim(rtrim(number_format($available, 1), '0'), '.') }}h</strong>
                    @if($allocation->isSubmitted())<span class="badge bg-success">{{ __('targets.cap.submitted_badge') }}</span>@else<span class="badge bg-secondary">{{ __('targets.cap.draft_badge') }}</span>@endif
                </span>
            </div>
            <form method="POST" action="{{ route('capacity.save') }}" id="alloc-form">
                @csrf
                <input type="hidden" name="week_start" value="{{ $week->toDateString() }}">
                <table class="table table-sm align-middle mb-2">
                    <tbody>
                    @foreach($categories as $cat)
                        <tr>
                            <td style="font-size:.85rem;">{{ $cat->localizedName() }}</td>
                            <td style="width:110px;">
                                <div class="input-group input-group-sm">
                                    <input type="number" min="0" max="100" step="1" name="allocations[{{ $cat->id }}]"
                                        aria-label="{{ $cat->localizedName() }} %"
                                        value="{{ optional($lineByCat->get($cat->id))->percent ? rtrim(rtrim(number_format($lineByCat->get($cat->id)->percent, 1), '0'), '.') : '' }}"
                                        class="form-control alloc-pct" data-cat="{{ $cat->id }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td class="text-end fw-bold">{{ __('targets.cap.total') }}</td>
                            <td><span id="alloc-sum" class="fw-bold">0</span>%</td></tr>
                    </tfoot>
                </table>
                <div class="d-flex gap-2">
                    <button name="submit" value="0" class="btn btn-outline-secondary flex-fill">{{ __('targets.cap.save_draft') }}</button>
                    <button name="submit" value="1" class="btn btn-primary flex-fill">{{ __('targets.cap.submit') }}</button>
                </div>
                <small class="text-muted d-block mt-2">{{ __('targets.cap.must_sum') }}</small>
            </form>
        </div></div>
    </div>

    {{-- Monthly rollup --}}
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-chart-pie"></i> {{ __('targets.cap.allocation_vs_intended') }}</span></div>
        <div class="card-content">
            @forelse($categories as $cat)
                @php $actual = $allocationByCat[$cat->id]['pct'] ?? 0; $want = $intended[$cat->id] ?? 0; @endphp
                @if($actual > 0 || $want > 0)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between" style="font-size:.82rem;">
                            <span>{{ $cat->localizedName() }}</span>
                            <span class="text-muted">{{ (int) $actual }}% @if($want > 0)/ {{ (int) $want }}% {{ __('targets.cap.intended') }}@endif</span>
                        </div>
                        <div class="cap-bar"><div class="cap-bar__fill" style="width: {{ min(100, $actual) }}%; background: {{ $cat->kind === 'delivery' ? 'var(--primary-bright)' : ($cat->kind === 'business_dev' ? 'var(--warning-color)' : 'var(--gray-400)') }};"></div>
                            @if($want > 0)<span class="cap-bar__target" style="inset-inline-start: {{ min(100, $want) }}%;"></span>@endif
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-muted mb-0" style="font-size:.85rem;">{{ __('targets.cap.no_logs') }}</p>
            @endforelse
            @if($availableMonth <= 0)<p class="text-muted mb-0 mt-2" style="font-size:.8rem;">{{ __('targets.cap.no_logs') }}</p>@endif
        </div></div>

        <div class="row g-3">
            <div class="col-sm-4"><div class="card h-100"><div class="card-content text-center">
                <div class="text-muted" style="font-size:.75rem;">{{ __('targets.cap.utilization') }}</div>
                <div style="font-size:1.5rem; font-weight:800; color:var(--{{ $utilRag }}-color);">{{ (int) $utilization }}%</div>
                <div class="text-muted" style="font-size:.7rem;">{{ __('targets.cap.target') }} {{ (int) $utilizationTarget }}%</div>
            </div></div></div>
            <div class="col-sm-4"><div class="card h-100"><div class="card-content text-center">
                <div class="text-muted" style="font-size:.75rem;">{{ __('targets.cap.revenue') }}</div>
                <div style="font-size:1.5rem; font-weight:800;">{{ number_format($revenue['total']) }}</div>
                <div class="text-muted" style="font-size:.7rem;">{{ config('scope.currency', 'SAR') }}</div>
            </div></div></div>
            <div class="col-sm-4"><div class="card h-100"><div class="card-content text-center">
                <div class="text-muted" style="font-size:.75rem;">{{ __('targets.cap.presales_output') }}</div>
                <div style="font-size:1.5rem; font-weight:800;">{{ $output['count'] }}</div>
                <div class="text-muted" style="font-size:.7rem;">{{ __('targets.cap.quotes') }}</div>
            </div></div></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.tgt-hero-stat{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); border-radius:var(--radius-lg); padding:.5rem 1.1rem; text-align:center; }
.cap-bar{ position:relative; height:10px; border-radius:999px; background:var(--gray-200); overflow:visible; margin-top:.2rem; }
.cap-bar__fill{ height:100%; border-radius:999px; }
.cap-bar__target{ position:absolute; top:-2px; width:2px; height:14px; background:var(--text-color); opacity:.6; }
</style>
@endpush
@push('scripts')
<script>
(function(){
    function recalc(){ var s=0; document.querySelectorAll('.alloc-pct').forEach(function(i){ s += parseFloat(i.value||0); });
        var el=document.getElementById('alloc-sum'); if(el){ el.textContent=Math.round(s*10)/10; el.style.color = (Math.round(s)===100)?'var(--success-color)':'var(--danger-color)'; } }
    document.querySelectorAll('.alloc-pct').forEach(function(i){ i.addEventListener('input', recalc); });
    recalc();
})();
</script>
@endpush
