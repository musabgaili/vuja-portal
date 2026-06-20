@extends('layouts.internal-dashboard')
@section('title', $quote->quote_number.' — '.__('portal.scope_planner.editor'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('scope-planner.index') }}">{{ __('portal.nav.scope_planner') }}</a></li>
<li class="breadcrumb-item active">{{ $quote->quote_number }}</li>
@endsection

@php
    $step = $step ?? 'items';
    $c = $quote->ai_content ?? [];
    $flatSections = match ($quote->customer_category) {
        'student' => ['subject' => 'string', 'needs' => 'string', 'scope_of_work' => 'array', 'out_of_scope' => 'array', 'notes' => 'array'],
        'entrepreneur' => ['introduction' => 'string', 'proposed_scope' => 'array', 'technical_specs' => 'array', 'mechanical_specs' => 'array', 'operational_logic' => 'array', 'implementation_phases' => 'array', 'out_of_scope' => 'array', 'notes' => 'array'],
        default => ['introduction_purpose' => 'string', 'out_of_scope' => 'array', 'notes' => 'array'],
    };
    $components = $quote->items->where('type', 'component')->values();
    $serviceItems = $quote->items->where('type', 'service')->values();
    $editable = in_array($quote->status, ['draft', 'changes_requested'], true);
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

{{-- 3-step progress --}}
<div class="scope-steps mb-3">
    <a href="{{ route('scope-planner.create') }}" class="scope-step done">
        <span class="n">1</span> {{ __('portal.scope_planner.step_brief') }}
    </a>
    <a href="{{ route('scope-planner.show', ['quote' => $quote, 'step' => 'items']) }}" class="scope-step {{ $step === 'items' ? 'active' : 'done' }}">
        <span class="n">2</span> {{ __('portal.scope_planner.step_items') }}
    </a>
    <a href="{{ route('scope-planner.show', ['quote' => $quote, 'step' => 'document']) }}" class="scope-step {{ $step === 'document' ? 'active' : (empty($c) ? '' : 'done') }}">
        <span class="n">3</span> {{ __('portal.scope_planner.step_document') }}
    </a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@unless($editable)
<div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span><i class="fas fa-lock"></i> {{ __('portal.scope_planner.locked_notice') }}</span>
    @if($quote->status !== 'accepted')
    <form method="POST" action="{{ route('scope-planner.reopen', $quote) }}" class="m-0">@csrf
        <button class="btn btn-sm btn-warning"><i class="fas fa-unlock"></i> {{ __('portal.scope_planner.reopen') }}</button>
    </form>
    @endif
</div>
@endunless

<div class="row g-3">
    {{-- LEFT: the active step --}}
    <div class="col-lg-7">
        @if($step === 'items')
        {{-- ============ STEP 2 · ITEMS ============ --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title"><i class="fas fa-list"></i> {{ __('portal.scope_planner.items') }}</span>
                @if($editable)
                <form method="POST" action="{{ route('scope-planner.suggest', $quote) }}" class="m-0">@csrf
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_planner.resuggest') }}</button>
                </form>
                @endif
            </div>
            <div class="card-content">
                @if($suggestion && ($suggestion['source'] ?? ''))
                <div class="alert alert-info py-2">
                    <i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_planner.ai_suggestion') }} ({{ $suggestion['source'] }}) — {{ __('portal.scope_planner.ai_suggestion_hint') }}
                </div>
                @endif
                <form method="POST" action="{{ route('scope-planner.items', $quote) }}" id="items-form">
                    @csrf @method('PUT')

                    <h6 class="fw-bold">{{ __('portal.scope_planner.components') }} <small class="text-muted">({{ __('portal.scope_planner.components_hint') }})</small></h6>
                    <table class="table table-sm" id="components-table">
                        <thead><tr><th>{{ __('portal.scope_planner.item') }}</th><th style="width:90px">{{ __('scope.qty') }}</th><th style="width:40px"></th></tr></thead>
                        <tbody>
                        @foreach($components as $i => $it)
                            <tr>
                                <td>
                                    <select name="components[{{ $i }}][stock_item_id]" class="form-select form-select-sm mb-1">
                                        <option value="">— {{ __('portal.scope_planner.manual') }} —</option>
                                        @foreach($stockItems as $s)<option value="{{ $s->id }}" @selected($it->stock_item_id==$s->id)>{{ $s->name }} — {{ $s->category }}</option>@endforeach
                                    </select>
                                    <input type="text" name="components[{{ $i }}][name]" value="{{ $it->name }}" class="form-control form-control-sm" placeholder="{{ __('portal.scope_planner.item_name') }}">
                                </td>
                                <td><input type="number" min="1" name="components[{{ $i }}][qty]" value="{{ (int) $it->qty }}" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                            </tr>
                        @endforeach
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
                                    <select name="services[{{ $i }}][pricing_rule_id]" class="form-select form-select-sm svc-select mb-1" onchange="fillRate(this)">
                                        <option value="">— {{ __('portal.scope_planner.manual') }} —</option>
                                        @foreach($services as $svc)<option value="{{ $svc->key }}" data-rate="{{ $svc->unitRate }}" @selected($it->pricing_rule_id==$svc->key)>{{ $svc->name(app()->getLocale()) }} ({{ number_format($svc->unitRate,0) }})</option>@endforeach
                                    </select>
                                    <input type="text" name="services[{{ $i }}][name]" value="{{ $it->name }}" class="form-control form-control-sm" placeholder="{{ __('portal.scope_planner.item_name') }}">
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

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="continue" value="0" class="btn btn-outline-primary"><i class="fas fa-save"></i> {{ __('portal.scope_planner.save_items') }}</button>
                        <button type="submit" name="continue" value="1" class="btn btn-primary"><i class="fas fa-arrow-right"></i> {{ __('portal.scope_planner.save_continue') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @else
        {{-- ============ STEP 3 · DOCUMENT ============ --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title"><i class="fas fa-pen-fancy"></i> {{ __('portal.scope_planner.document_sections') }}</span>
                @if($editable)
                <form method="POST" action="{{ route('scope-planner.generate', $quote) }}" class="m-0">@csrf
                    <button class="btn btn-sm btn-primary"><i class="fas fa-wand-magic-sparkles"></i> {{ empty($c) ? __('portal.scope_planner.generate') : __('portal.scope_planner.regenerate_all') }}</button>
                </form>
                @endif
            </div>
            <div class="card-content">
                @if(empty($c))
                    <p class="text-muted">{{ __('portal.scope_planner.generate_hint') }}</p>
                    <a href="{{ route('scope-planner.show', ['quote' => $quote, 'step' => 'items']) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ __('portal.scope_planner.back_to_items') }}</a>
                @else
                <form method="POST" action="{{ route('scope-planner.update', $quote) }}">
                    @csrf @method('PUT')
                    @foreach($flatSections as $key => $type)
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-1">{{ __('scope.'.$key) }}</label>
                            @if($type === 'array')<input type="hidden" name="array_sections[]" value="{{ $key }}">@endif
                            <textarea name="sections[{{ $key }}]" data-autosize rows="{{ $type === 'array' ? 5 : 3 }}" class="form-control" @unless($editable)readonly @endunless placeholder="{{ $type === 'array' ? __('portal.scope_planner.one_per_line') : '' }}">{{ $type === 'array' ? implode("\n", (array) ($c[$key] ?? [])) : ($c[$key] ?? '') }}</textarea>
                        </div>
                    @endforeach

                    {{-- Company: editable per-scope sections --}}
                    @if($quote->customer_category === 'company' && $quote->scopes->isNotEmpty())
                        <hr>
                        <h6 class="fw-bold">{{ __('portal.scope_planner.scopes') }}</h6>
                        @foreach($quote->scopes as $s)
                        <div class="border rounded p-2 mb-3">
                            <label class="form-label fw-bold mb-1">{{ __('portal.scope_planner.scope_title') }} #{{ $loop->iteration }}</label>
                            <input type="text" name="scopes[{{ $s->id }}][title]" value="{{ $s->title }}" class="form-control form-control-sm mb-2" @unless($editable)readonly @endunless>
                            @foreach(['objective','inputs_required','deliverables','acceptance_criteria','exclusions'] as $f)
                                <label class="form-label small mb-0">{{ __('scope.'.$f) }} <small class="text-muted">({{ __('portal.scope_planner.one_per_line') }})</small></label>
                                <textarea name="scopes[{{ $s->id }}][{{ $f }}]" data-autosize rows="3" class="form-control form-control-sm mb-2" @unless($editable)readonly @endunless>{{ implode("\n", (array) (is_array($s->$f) ? $s->$f : array_filter([$s->$f]))) }}</textarea>
                            @endforeach
                        </div>
                        @endforeach
                    @endif

                    @if($editable)
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.scope_planner.save_sections') }}</button>
                        <a href="{{ route('scope-planner.show', ['quote' => $quote, 'step' => 'items']) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ __('portal.scope_planner.back_to_items') }}</a>
                    </div>
                    @endif
                </form>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: totals + preview (both steps) --}}
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

                @if($editable && $step === 'document' && ! empty($c))
                <div class="d-flex gap-2 flex-wrap mt-3">
                    <a href="{{ route('quotes.show', $quote) }}" class="btn btn-outline-secondary"><i class="fas fa-floppy-disk"></i> {{ __('portal.scope_planner.save_draft') }}</a>
                    <form method="POST" action="{{ route('scope-planner.finalize', $quote) }}" class="m-0">@csrf
                        <button class="btn btn-success" onclick="return confirm(@json(__('portal.scope_planner.finalize_confirm')))"><i class="fas fa-lock"></i> {{ __('portal.scope_planner.finalize') }}</button>
                    </form>
                </div>
                <small class="text-muted d-block mt-1">{{ __('portal.scope_planner.finalize_hint') }}</small>
                @elseif($step === 'document' && empty($c))
                {{-- nothing yet --}}
                @else
                <a href="{{ route('quotes.show', $quote) }}" class="btn btn-outline-secondary mt-3"><i class="fas fa-floppy-disk"></i> {{ __('portal.scope_planner.save_draft') }}</a>
                @endif
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

<style>
.scope-steps { display:flex; gap:.5rem; flex-wrap:wrap; }
.scope-steps .scope-step { flex:1; min-width:120px; display:flex; align-items:center; gap:.5rem; padding:.6rem .9rem; border:1px solid var(--gray-200,#e2e8f0); border-radius:10px; text-decoration:none; color:var(--gray-500,#64748b); font-weight:600; background:var(--bg-secondary,#fff); }
.scope-steps .scope-step .n { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:var(--gray-200,#e2e8f0); color:var(--gray-600,#475569); font-size:.8rem; }
.scope-steps .scope-step.active { border-color:var(--primary-color,#0C7075); color:var(--primary-color,#0C7075); }
.scope-steps .scope-step.active .n { background:var(--primary-color,#0C7075); color:#fff; }
.scope-steps .scope-step.done { color:var(--text-color,#334155); }
.scope-steps .scope-step.done .n { background:#10b981; color:#fff; }
</style>
@endsection

@push('scripts')
<script>
let compIdx = {{ $components->count() }};
let svcIdx = {{ $serviceItems->count() }};
const stockOptions = `@foreach($stockItems as $s)<option value="{{ $s->id }}">{{ $s->name }} — {{ $s->category }}</option>@endforeach`;
const svcOptions = `@foreach($services as $svc)<option value="{{ $svc->key }}" data-rate="{{ $svc->unitRate }}">{{ $svc->name(app()->getLocale()) }} ({{ number_format($svc->unitRate,0) }})</option>@endforeach`;
const L_MANUAL = @json('— '.__('portal.scope_planner.manual').' —');
const L_NAME = @json(__('portal.scope_planner.item_name'));

function addComponentRow() {
    const tbody = document.querySelector('#components-table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="components[${compIdx}][stock_item_id]" class="form-select form-select-sm mb-1"><option value="">${L_MANUAL}</option>${stockOptions}</select>`
        + `<input type="text" name="components[${compIdx}][name]" value="" class="form-control form-control-sm" placeholder="${L_NAME}"></td>`
        + `<td><input type="number" min="1" name="components[${compIdx}][qty]" value="1" class="form-control form-control-sm"></td>`
        + `<td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>`;
    tbody.appendChild(tr); compIdx++;
}
function addServiceRow() {
    const tbody = document.querySelector('#services-table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="services[${svcIdx}][pricing_rule_id]" class="form-select form-select-sm svc-select mb-1" onchange="fillRate(this)"><option value="">${L_MANUAL}</option>${svcOptions}</select>`
        + `<input type="text" name="services[${svcIdx}][name]" value="" class="form-control form-control-sm" placeholder="${L_NAME}"></td>`
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
// Auto-grow editable section textareas so the full content is visible.
function autosize(el) { el.style.height = 'auto'; el.style.height = (el.scrollHeight + 4) + 'px'; }
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('textarea[data-autosize]').forEach(function (t) { autosize(t); t.addEventListener('input', function () { autosize(t); }); });
});
</script>
@endpush
