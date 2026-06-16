@extends('layouts.internal-dashboard')
@section('title', __('targets.admin.metrics'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-sliders"></i> {{ __('targets.admin.metrics') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('targets.admin.metrics_subtitle') }}</p>
    </div>
    <a href="{{ route('targets.admin.index') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('targets.admin.title') }}</a>
</div>


<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-plus"></i> {{ __('targets.admin.add_metric') }}</span></div>
        <div class="card-content">
            <form method="POST" action="{{ route('targets.admin.metrics.store') }}">@csrf
                <label class="form-label">{{ __('targets.admin.metric_name') }}</label>
                <input type="text" name="name" class="form-control mb-2" required maxlength="80">
                <label class="form-label">{{ __('targets.admin.metric_name_ar') }}</label>
                <input type="text" name="name_ar" class="form-control mb-2" maxlength="80" dir="rtl">
                <label class="form-label">{{ __('targets.admin.unit') }}</label>
                <select name="unit" class="form-select mb-2"><option value="count">{{ __('targets.admin.unit_count') }}</option><option value="sar">{{ __('targets.admin.unit_sar') }}</option></select>
                <small class="text-muted d-block mb-2">{{ __('targets.admin.manual_metric_hint') }}</small>
                <button class="btn btn-primary w-100">{{ __('targets.admin.add_metric') }}</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        @foreach($metrics as $metric)<form id="m-{{ $metric->id }}" method="POST" action="{{ route('targets.admin.metrics.update', $metric) }}" class="d-none">@csrf @method('PUT')</form>@endforeach
        <div class="card"><div class="card-content p-0" style="overflow-x:auto;">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <th>{{ __('targets.admin.metric_name') }}</th>
                    <th>{{ __('targets.admin.metric_name_ar') }}</th>
                    <th>{{ __('targets.admin.source') }}</th>
                    <th>{{ __('targets.admin.unit') }}</th>
                    <th style="width:70px;">{{ __('targets.admin.active') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($metrics as $metric)
                    @php $f = 'm-'.$metric->id; @endphp
                    <tr>
                        <td><input form="{{ $f }}" type="text" name="name" value="{{ $metric->name }}" class="form-control form-control-sm" required></td>
                        <td><input form="{{ $f }}" type="text" name="name_ar" value="{{ $metric->name_ar }}" class="form-control form-control-sm" dir="rtl"></td>
                        <td><span class="badge bg-{{ $metric->isAuto() ? 'info' : 'secondary' }}">{{ $metric->isAuto() ? __('targets.auto') : __('targets.manual') }}</span></td>
                        <td><span class="text-muted" style="font-size:.8rem;">{{ $metric->isCurrency() ? __('targets.admin.unit_sar') : __('targets.admin.unit_count') }}</span></td>
                        <td><input form="{{ $f }}" type="checkbox" name="is_active" value="1" @checked($metric->is_active)></td>
                        <td class="text-end"><button form="{{ $f }}" class="btn btn-sm btn-primary">{{ __('targets.admin.save') }}</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
</div>
@endsection
