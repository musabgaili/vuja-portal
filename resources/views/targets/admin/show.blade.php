@extends('layouts.internal-dashboard')
@section('title', $user->name)

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-chart-line"></i> {{ $user->name }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.admin.person_subtitle', ['month' => $month->translatedFormat('F Y')]) }}</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('targets.admin.record-month') }}">@csrf
            <input type="hidden" name="period" value="{{ $month->toDateString() }}">
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <button class="btn btn-success btn-sm"><i class="fas fa-camera"></i> {{ __('targets.admin.record_person') }}</button>
        </form>
        <a href="{{ route('targets.admin.index', ['month' => $month->format('Y-m')]) }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.admin.title') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@if($summary->isEmpty())
    <div class="card"><div class="card-content text-center py-5">
        <p class="text-muted mb-0">{{ __('targets.admin.no_targets_person') }}</p>
    </div></div>
@else
    <div class="row g-3">
        @foreach($summary as $row)
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-content">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-0">{{ $row->metric->localizedName() }}</h5>
                            <span class="text-muted" style="font-size:.8rem;">{{ __('targets.progress_label', ['actual' => rtrim(rtrim(number_format($row->actual, 2), '0'), '.'), 'target' => rtrim(rtrim(number_format($row->target_value, 2), '0'), '.')]) }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $row->rag === 'green' ? 'success' : ($row->rag === 'amber' ? 'warning' : 'danger') }}">{{ (int) $row->attainment }}%</span>
                            <form method="POST" action="{{ route('targets.admin.delete', $row->target) }}" onsubmit="return confirm('{{ __('targets.admin.delete_confirm') }}')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="{{ __('targets.admin.remove') }}"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @if(isset($trends[$row->metric->id]))
                        @include('partials.target-trend', ['points' => $trends[$row->metric->id]])
                    @endif
                </div></div>
            </div>
        @endforeach
    </div>
@endif
@endsection
