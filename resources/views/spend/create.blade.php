@extends('layouts.internal-dashboard')
@section('title', __('portal.spend.new_request'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('spend.index') }}">{{ __('portal.spend.my_title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.spend.new_request') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-receipt"></i> {{ __('portal.spend.new_request') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.spend.new_sub') }}</p>
</div>

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<form method="POST" action="{{ route('spend.store') }}" enctype="multipart/form-data" class="card">
    @csrf
    <div class="card-content">
        <div class="row g-3">
            {{-- Type --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('portal.spend.type_label') }} *</label>
                <select name="type" id="sp_type" class="form-select" onchange="spToggle()">
                    <option value="reimbursement" @selected(old('type')==='reimbursement')>{{ __('portal.spend.type.reimbursement') }}</option>
                    <option value="purchase" @selected(old('type')==='purchase')>{{ __('portal.spend.type.purchase') }}</option>
                </select>
                <small class="text-muted" id="sp_type_hint">{{ __('portal.spend.type_hint_reimbursement') }}</small>
            </div>
            {{-- Scope --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('portal.spend.scope_label') }} *</label>
                <select name="scope" id="sp_scope" class="form-select" onchange="spToggle()">
                    <option value="project" @selected(old('scope', 'project')==='project')>{{ __('portal.spend.scope.project') }}</option>
                    <option value="general" @selected(old('scope')==='general')>{{ __('portal.spend.scope.general') }}</option>
                </select>
            </div>

            {{-- Project (scope=project) --}}
            <div class="col-md-6 sp-project">
                <label class="form-label fw-bold">{{ __('portal.spend.project') }} *</label>
                <select name="project_id" class="form-select">
                    <option value="">{{ __('portal.spend.choose_project') }}</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(old('project_id')==$p->id)>{{ $p->title }}</option>
                    @endforeach
                </select>
                @if($projects->isEmpty())<small class="text-warning">{{ __('portal.spend.no_projects_hint') }}</small>@endif
            </div>
            {{-- Category (scope=general) --}}
            <div class="col-md-6 sp-general">
                <label class="form-label fw-bold">{{ __('portal.spend.category') }}</label>
                <select name="category" class="form-select">
                    <option value="">{{ __('portal.spend.choose_category') }}</option>
                    @foreach($categories as $c)
                        <option value="{{ $c }}" @selected(old('category')===$c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.spend.col_title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255" placeholder="{{ __('portal.spend.title_ph') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.spend.amount_label') }} ({{ config('scope.currency','SAR') }}) *</label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}" required>
                <small class="text-muted" id="sp_amount_hint">{{ __('portal.spend.amount_hint_reimbursement') }}</small>
            </div>

            {{-- Purchase-only --}}
            <div class="col-md-5 sp-purchase">
                <label class="form-label fw-bold">{{ __('portal.spend.buy_link') }}</label>
                <input type="url" name="product_url" class="form-control" value="{{ old('product_url') }}" placeholder="https://…">
            </div>
            <div class="col-md-3 sp-purchase">
                <label class="form-label fw-bold">{{ __('portal.spend.quantity') }}</label>
                <input type="number" min="1" name="quantity" class="form-control" value="{{ old('quantity', 1) }}">
            </div>

            {{-- Reimbursement-only --}}
            <div class="col-md-8 sp-reimbursement">
                <label class="form-label fw-bold">{{ __('portal.spend.receipt') }}</label>
                <input type="file" name="receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">{{ __('portal.spend.receipt_hint') }}</small>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.spend.description') }}</label>
                <textarea name="description" rows="3" class="form-control" maxlength="5000" placeholder="{{ __('portal.spend.description_ph') }}">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('spend.index') }}" class="btn btn-secondary">{{ __('portal.spend.cancel') }}</a>
        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.spend.submit') }}</button>
    </div>
</form>

<script>
function spToggle() {
    var type = document.getElementById('sp_type').value;
    var scope = document.getElementById('sp_scope').value;
    document.querySelectorAll('.sp-purchase').forEach(function (e) { e.style.display = type === 'purchase' ? '' : 'none'; });
    document.querySelectorAll('.sp-reimbursement').forEach(function (e) { e.style.display = type === 'reimbursement' ? '' : 'none'; });
    document.querySelectorAll('.sp-project').forEach(function (e) { e.style.display = scope === 'project' ? '' : 'none'; });
    document.querySelectorAll('.sp-general').forEach(function (e) { e.style.display = scope === 'general' ? '' : 'none'; });
    document.getElementById('sp_type_hint').textContent = @json(__('portal.spend.type_hint_reimbursement')) ;
    if (type === 'purchase') document.getElementById('sp_type_hint').textContent = @json(__('portal.spend.type_hint_purchase'));
    document.getElementById('sp_amount_hint').textContent = type === 'purchase' ? @json(__('portal.spend.amount_hint_purchase')) : @json(__('portal.spend.amount_hint_reimbursement'));
}
document.addEventListener('DOMContentLoaded', spToggle);
</script>
@endsection
