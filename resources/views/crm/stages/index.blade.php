@extends('layouts.internal-dashboard')
@section('title', __('portal.crm_stages.title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-sliders"></i> {{ __('portal.crm_stages.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.crm_stages.subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

{{-- Per-row forms live outside the table; inputs reference them via the HTML5 form="" attribute. --}}
@foreach($stages as $stage)
    <form id="update-{{ $stage->id }}" method="POST" action="{{ route('crm-stages.update', $stage) }}" class="d-none">@csrf @method('PUT')</form>
    <form id="move-up-{{ $stage->id }}" method="POST" action="{{ route('crm-stages.move', $stage) }}" class="d-none">@csrf<input type="hidden" name="direction" value="up"></form>
    <form id="move-down-{{ $stage->id }}" method="POST" action="{{ route('crm-stages.move', $stage) }}" class="d-none">@csrf<input type="hidden" name="direction" value="down"></form>
    <form id="delete-{{ $stage->id }}" method="POST" action="{{ route('crm-stages.destroy', $stage) }}" class="d-none" onsubmit="return confirm('{{ __('portal.crm_stages.delete_confirm') }}');">@csrf @method('DELETE')</form>
@endforeach

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.crm_stages.pipeline_stages') }}</span></div>
            <div class="card-content p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px;">{{ __('portal.crm_stages.order') }}</th>
                                <th>{{ __('portal.crm_stages.label') }}</th>
                                <th style="width:150px;">{{ __('portal.crm_stages.color') }}</th>
                                <th style="width:80px;" class="text-center">{{ __('portal.crm_stages.active') }}</th>
                                <th style="width:70px;" class="text-center">{{ __('portal.crm_stages.deals') }}</th>
                                <th style="width:110px;" class="text-end">{{ __('portal.crm_stages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stages as $stage)
                                @php $count = (int) ($counts[$stage->key] ?? 0); @endphp
                                <tr>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="submit" form="move-up-{{ $stage->id }}" class="btn btn-outline-secondary" title="{{ __('portal.crm_stages.move_up') }}" @disabled($loop->first)><i class="fas fa-chevron-up"></i></button>
                                            <button type="submit" form="move-down-{{ $stage->id }}" class="btn btn-outline-secondary" title="{{ __('portal.crm_stages.move_down') }}" @disabled($loop->last)><i class="fas fa-chevron-down"></i></button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="label" form="update-{{ $stage->id }}" value="{{ $stage->label }}" class="form-control form-control-sm" required maxlength="60">
                                        <small class="text-muted"><code>{{ $stage->key }}</code></small>
                                    </td>
                                    <td>
                                        <select name="color" form="update-{{ $stage->id }}" class="form-select form-select-sm">
                                            @foreach($colors as $c)
                                                <option value="{{ $c }}" @selected($stage->color === $c)>{{ __('portal.crm_stages.c.'.$c) }}</option>
                                            @endforeach
                                        </select>
                                        <span class="badge bg-{{ $stage->color }} mt-1">{{ $stage->label }}</span>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="is_active" value="0" form="update-{{ $stage->id }}">
                                        <input type="checkbox" name="is_active" value="1" form="update-{{ $stage->id }}" @checked($stage->is_active)>
                                    </td>
                                    <td class="text-center">{{ $count }}</td>
                                    <td class="text-end">
                                        <button type="submit" form="update-{{ $stage->id }}" class="btn btn-sm btn-primary" title="{{ __('portal.crm_stages.save') }}"><i class="fas fa-save"></i></button>
                                        <button type="submit" form="delete-{{ $stage->id }}" class="btn btn-sm btn-outline-danger" title="{{ __('portal.crm_stages.delete') }}" @disabled($count > 0)><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <p class="text-muted mt-2" style="font-size:.8rem;">{{ __('portal.crm_stages.delete_hint') }}</p>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.crm_stages.add_stage') }}</span></div>
            <div class="card-content">
                <form method="POST" action="{{ route('crm-stages.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('portal.crm_stages.label') }}</label>
                        <input type="text" name="label" class="form-control" required maxlength="60" value="{{ old('label') }}" placeholder="{{ __('portal.crm_stages.label_ph') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('portal.crm_stages.color') }}</label>
                        <select name="color" class="form-select">
                            @foreach($colors as $c)
                                <option value="{{ $c }}" @selected(old('color', 'secondary') === $c)>{{ __('portal.crm_stages.c.'.$c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> {{ __('portal.crm_stages.add_stage') }}</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><span class="card-title">{{ __('portal.crm_stages.terminal') }}</span></div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('portal.crm_stages.terminal_hint') }}</p>
                <div class="d-flex justify-content-between"><span class="badge bg-success">{{ __('portal.crm_stages.won') }}</span><span>{{ $wonCount }}</span></div>
                <div class="d-flex justify-content-between mt-2"><span class="badge bg-danger">{{ __('portal.crm_stages.lost') }}</span><span>{{ $lostCount }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
