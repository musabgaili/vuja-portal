@extends('layouts.internal-dashboard')
@section('title', $quote->quote_number.' — '.__('portal.scope_planner.editor'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('scope-planner.index') }}">{{ __('portal.nav.scope_planner') }}</a></li>
<li class="breadcrumb-item active">{{ $quote->quote_number }}</li>
@endsection

@php
    $c = $quote->ai_content ?? [];
    $flatSections = match ($quote->customer_category) {
        'student' => ['subject' => 'string', 'needs' => 'string', 'scope_of_work' => 'array', 'out_of_scope' => 'array', 'notes' => 'array'],
        'entrepreneur' => ['introduction' => 'string', 'proposed_scope' => 'array', 'technical_specs' => 'array', 'mechanical_specs' => 'array', 'operational_logic' => 'array', 'implementation_phases' => 'array', 'out_of_scope' => 'array', 'notes' => 'array'],
        default => ['introduction_purpose' => 'string'],
    };
    $components = $quote->items->where('type', 'component')->values();
    $serviceItems = $quote->items->where('type', 'service')->values();
@endphp

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-file-invoice"></i> {{ $quote->quote_number }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">
            {{ __('portal.scope_planner.tier_'.$quote->customer_category) }} ·
            {{ strtoupper($quote->language) }} ·
            <span class="badge bg-{{ $quote->statusColor() }}">{{ __('portal.quote.status.'.$quote->status) }}</span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('scope-planner.document', $quote) }}" target="_blank" class="btn btn-light"><i class="fas fa-up-right-from-square"></i> {{ __('portal.scope_planner.open_document') }}</a>
        <a href="{{ route('scope-planner.view.pdf', $quote) }}" target="_blank" class="btn btn-light"><i class="fas fa-file-pdf"></i> {{ __('portal.scope_planner.view_pdf') }}</a>
        <a href="{{ route('scope-planner.export.pdf', $quote) }}" class="btn btn-light"><i class="fas fa-download"></i> PDF</a>
        <a href="{{ route('scope-planner.export.docx', $quote) }}" class="btn btn-light"><i class="fas fa-file-word"></i> DOCX</a>
        @if($quote->customer_category === 'company')
        <a href="{{ route('scope-planner.technical.pdf', $quote) }}" class="btn btn-light"><i class="fas fa-microchip"></i> {{ __('portal.scope_planner.technical_offer') }}</a>
        @endif
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@if($suggestion && ($suggestion['source'] ?? '') )
<div class="alert alert-info" data-persist>
    <strong><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_planner.ai_suggestion') }}</strong>
    ({{ $suggestion['source'] }})
    — {{ __('portal.scope_planner.ai_suggestion_hint') }}
</div>
@endif

<div class="row g-3">
    {{-- LEFT: build the quote --}}
    <div class="col-lg-7">
        {{-- Items --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title"><i class="fas fa-list"></i> {{ __('portal.scope_planner.items') }}</span>
                <form method="POST" action="{{ route('scope-planner.suggest', $quote) }}" class="m-0">@csrf
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_planner.resuggest') }}</button>
                </form>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('scope-planner.items', $quote) }}">
                    @csrf @method('PUT')

                    <h6 class="fw-bold">{{ __('portal.scope_planner.components') }} <small class="text-muted">({{ __('portal.scope_planner.components_hint') }})</small></h6>
                    <table class="table table-sm" id="components-table">
                        <thead><tr><th>{{ __('portal.scope_planner.item') }}</th><th style="width:90px">{{ __('scope.qty') }}</th><th style="width:40px"></th></tr></thead>
                        <tbody>
                        @forelse($components as $i => $it)
                            <tr>
                                <td>
                                    <select name="components[{{ $i }}][stock_item_id]" class="form-select form-select-sm">
                                        <option value="">{{ $it->name }} ({{ __('portal.scope_planner.manual') }})</option>
                                        @foreach($stockItems as $s)<option value="{{ $s->id }}" @selected($it->stock_item_id==$s->id)>{{ $s->name }} — {{ $s->category }}</option>@endforeach
                                    </select>
                                    <input type="hidden" name="components[{{ $i }}][name]" value="{{ $it->name }}">
                                </td>
                                <td><input type="number" min="1" name="components[{{ $i }}][qty]" value="{{ (int) $it->qty }}" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addComponentRow()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.add_component') }}</button>

                    <h6 class="fw-bold">{{ __('portal.scope_planner.services') }}</h6>
                    <table class="table table-sm" id="services-table">
                        <thead><tr><th>{{ __('portal.scope_planner.service') }}</th><th style="width:80px">{{ __('scope.qty') }}</th><th style="width:120px">{{ __('scope.unit_price') }}</th><th style="width:40px"></th></tr></thead>
                        <tbody>
                        @foreach($serviceItems as $i => $it)
                            <tr>
                                <td>
                                    <select name="services[{{ $i }}][pricing_rule_id]" class="form-select form-select-sm svc-select" onchange="fillRate(this)">
                                        <option value="">{{ $it->name }} ({{ __('portal.scope_planner.manual') }})</option>
                                        @foreach($services as $svc)<option value="{{ $svc->key }}" data-rate="{{ $svc->unitRate }}" @selected($it->pricing_rule_id==$svc->key)>{{ $svc->name(app()->getLocale()) }} ({{ number_format($svc->unitRate,0) }})</option>@endforeach
                                    </select>
                                    <input type="hidden" name="services[{{ $i }}][name]" value="{{ $it->name }}">
                                </td>
                                <td><input type="number" min="1" name="services[{{ $i }}][qty]" value="{{ (int) $it->qty }}" class="form-control form-control-sm"></td>
                                <td><input type="number" step="0.01" min="0" name="services[{{ $i }}][unit_price]" value="{{ (float) $it->unit_price }}" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addServiceRow()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.add_service') }}</button>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('portal.scope_planner.components_override') }}</label>
                        <input type="number" step="0.01" min="0" name="components_client_total" value="{{ $quote->components_client_total }}" class="form-control" placeholder="{{ __('portal.scope_planner.components_override_ph') }}">
                        <small class="text-muted">{{ __('portal.scope_planner.components_override_hint') }}</small>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.scope_planner.save_items') }}</button>
                </form>
            </div>
        </div>

        {{-- Generate + edit sections --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title"><i class="fas fa-pen-fancy"></i> {{ __('portal.scope_planner.document_sections') }}</span>
                <form method="POST" action="{{ route('scope-planner.generate', $quote) }}" class="m-0">@csrf
                    <button class="btn btn-sm btn-primary"><i class="fas fa-wand-magic-sparkles"></i> {{ empty($c) ? __('portal.scope_planner.generate') : __('portal.scope_planner.regenerate_all') }}</button>
                </form>
            </div>
            <div class="card-content">
                @if(empty($c))
                    <p class="text-muted">{{ __('portal.scope_planner.generate_hint') }}</p>
                @else
                <form method="POST" action="{{ route('scope-planner.update', $quote) }}">
                    @csrf @method('PUT')
                    @foreach($flatSections as $key => $type)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-bold mb-0">{{ __('scope.'.$key) }}</label>
                            </div>
                            @if($type === 'array')<input type="hidden" name="array_sections[]" value="{{ $key }}">@endif
                            <textarea name="sections[{{ $key }}]" rows="{{ $type === 'array' ? 4 : 2 }}" class="form-control" placeholder="{{ $type === 'array' ? __('portal.scope_planner.one_per_line') : '' }}">{{ $type === 'array' ? implode("\n", (array) ($c[$key] ?? [])) : ($c[$key] ?? '') }}</textarea>
                        </div>
                    @endforeach

                    @if($quote->customer_category === 'company' && $quote->scopes->isNotEmpty())
                        <div class="alert alert-light border"><strong>{{ __('portal.scope_planner.scopes') }}:</strong>
                            {{ $quote->scopes->pluck('title')->implode(' · ') }}
                            <br><small class="text-muted">{{ __('portal.scope_planner.scopes_hint') }}</small>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.scope_planner.save_sections') }}</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- RIGHT: totals + preview --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title"><i class="fas fa-calculator"></i> {{ __('portal.scope_planner.totals') }}</span></div>
            <div class="card-content">
                <table class="table table-sm mb-2">
                    <tr><td>{{ __('scope.subtotal') }}</td><td class="text-end">{{ number_format($quote->subtotal, 2) }} {{ config('scope.currency') }}</td></tr>
                    <tr><td>{{ __('scope.vat', ['rate' => rtrim(rtrim(number_format($quote->vat_rate,2),'0'),'.')]) }}</td><td class="text-end">{{ number_format($quote->vat_amount, 2) }}</td></tr>
                    <tr class="fw-bold"><td>{{ __('scope.grand_total') }}</td><td class="text-end">{{ number_format($quote->grand_total, 2) }}</td></tr>
                </table>
                @if($quote->milestones->isNotEmpty())
                <h6 class="fw-bold mt-3">{{ __('scope.payment_schedule') }}</h6>
                <ul class="list-unstyled mb-0">
                    @foreach($quote->milestones as $m)
                    <li class="d-flex justify-content-between"><span>{{ $m->code }} · {{ rtrim(rtrim(number_format($m->percentage,2),'0'),'.') }}%</span><span>{{ number_format($m->amount, 2) }}</span></li>
                    @endforeach
                </ul>
                @endif
                <form method="POST" action="{{ route('scope-planner.finalize', $quote) }}" class="mt-3">@csrf
                    <button class="btn btn-success w-100"><i class="fas fa-lock"></i> {{ __('portal.scope_planner.finalize') }}</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title"><i class="fas fa-eye"></i> {{ __('portal.scope_planner.live_preview') }}</span>
                <a href="{{ route('scope-planner.document', $quote) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('portal.scope_planner.open_full') }}</a>
            </div>
            <div class="card-content p-0">
                <iframe src="{{ route('scope-planner.document', $quote) }}" style="width:100%;height:560px;border:0;border-radius:0 0 12px 12px;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let compIdx = {{ $components->count() }};
let svcIdx = {{ $serviceItems->count() }};
const stockOptions = `@foreach($stockItems as $s)<option value="{{ $s->id }}">{{ $s->name }} — {{ $s->category }}</option>@endforeach`;
const svcOptions = `@foreach($services as $svc)<option value="{{ $svc->key }}" data-rate="{{ $svc->unitRate }}">{{ $svc->name(app()->getLocale()) }} ({{ number_format($svc->unitRate,0) }})</option>@endforeach`;

function addComponentRow() {
    const tbody = document.querySelector('#components-table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="components[${compIdx}][stock_item_id]" class="form-select form-select-sm"><option value="">{{ __('portal.scope_planner.manual') }}</option>${stockOptions}</select></td>`
        + `<td><input type="number" min="1" name="components[${compIdx}][qty]" value="1" class="form-control form-control-sm"></td>`
        + `<td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>`;
    tbody.appendChild(tr); compIdx++;
}
function addServiceRow() {
    const tbody = document.querySelector('#services-table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="services[${svcIdx}][pricing_rule_id]" class="form-select form-select-sm svc-select" onchange="fillRate(this)"><option value="">{{ __('portal.scope_planner.manual') }}</option>${svcOptions}</select></td>`
        + `<td><input type="number" min="1" name="services[${svcIdx}][qty]" value="1" class="form-control form-control-sm"></td>`
        + `<td><input type="number" step="0.01" min="0" name="services[${svcIdx}][unit_price]" value="0" class="form-control form-control-sm"></td>`
        + `<td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>`;
    tbody.appendChild(tr); svcIdx++;
}
function fillRate(sel) {
    const opt = sel.options[sel.selectedIndex];
    const rate = opt ? opt.getAttribute('data-rate') : null;
    if (rate) { const price = sel.closest('tr').querySelector('input[name*="[unit_price]"]'); if (price && (!price.value || price.value === '0')) price.value = rate; }
}
</script>
@endpush
