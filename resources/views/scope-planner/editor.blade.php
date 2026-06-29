@extends('layouts.internal-dashboard')
@section('title', $quote->quote_number.' — '.__('portal.scope_planner.editor'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('scope-planner.index') }}">{{ __('portal.nav.scope_planner') }}</a></li>
<li class="breadcrumb-item active">{{ $quote->quote_number }}</li>
@endsection

@php
    $step = $step ?? 'items';
    $c = $quote->ai_content ?? [];
    // Canonical Step-3 section set per tier (shared with the AI prompt + document
    // blades via config so the generated text matches what the document renders).
    $flatSections = config('scope.sections.'.$quote->customer_category, config('scope.sections.company'));
    $components = $quote->items->where('type', 'component')->values();
    $serviceItems = $quote->items->where('type', 'service')->values();
    $editable = in_array($quote->status, ['draft', 'changes_requested'], true);
    // Pre-built for the row-builder JS (passed via @json as plain variables — the
    // @json directive splits its argument on commas, so the map must live here).
    // Components carry the tier price so selecting an inventory item auto-fills it;
    // services carry the rate + the Pricing-Tool description for auto-recall.
    $stockJson = $stockItems->map(fn ($s) => ['id' => $s->id, 'label' => $s->name.' — '.$s->category, 'price' => (float) $s->priceFor($quote->customer_category)])->values();
    $svcJson = collect($services)->map(fn ($s) => ['key' => $s->key, 'rate' => $s->unitRate, 'desc' => $s->description, 'label' => $s->name(app()->getLocale()).' ('.number_format($s->unitRate, 0).')'])->values();
    // Curated per-tier set of renamable headings + table column headers for the
    // targeted "rename labels" editor (stored in doc_labels). Each is a scope.* key.
    $labelKeys = match ($quote->customer_category) {
        'company' => ['scope.introduction_purpose', 'scope.pricing_structure', 'scope.scope', 'scope.type', 'scope.value', 'scope.objective', 'scope.inputs_required', 'scope.deliverables', 'scope.acceptance_criteria', 'scope.exclusions', 'scope.commercial_proposal', 'scope.price_summary', 'scope.payment_schedule', 'scope.milestone', 'scope.trigger', 'scope.amount', 'scope.indicative_timeline', 'scope.period', 'scope.activity', 'scope.out_of_scope', 'scope.notes', 'scope.general_terms'],
        'student' => ['scope.scope_of_work', 'scope.pricing', 'scope.description', 'scope.unit', 'scope.qty', 'scope.unit_price', 'scope.total', 'scope.out_of_scope', 'scope.notes'],
        default => ['scope.introduction', 'scope.proposed_scope', 'scope.technical_specs', 'scope.mechanical_specs', 'scope.operational_logic', 'scope.implementation_phases', 'scope.pricing', 'scope.payment_schedule', 'scope.out_of_scope', 'scope.notes'],
    };
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
                        <thead><tr><th>{{ __('portal.scope_planner.item') }}</th><th style="width:80px">{{ __('scope.qty') }}</th><th style="width:120px">{{ __('scope.unit_price') }}</th><th style="width:40px"></th></tr></thead>
                        <tbody>
                        @foreach($components as $i => $it)
                            <tr>
                                <td>
                                    <select name="components[{{ $i }}][stock_item_id]" class="form-select form-select-sm comp-select mb-1" onchange="fillComponentPrice(this)">
                                        <option value="">— {{ __('portal.scope_planner.manual') }} —</option>
                                        @foreach($stockItems as $s)<option value="{{ $s->id }}" data-price="{{ $s->priceFor($quote->customer_category) }}" @selected($it->stock_item_id==$s->id)>{{ $s->name }} — {{ $s->category }}</option>@endforeach
                                    </select>
                                    <input type="text" name="components[{{ $i }}][name]" value="{{ $it->name }}" class="form-control form-control-sm" placeholder="{{ __('portal.scope_planner.item_name') }}">
                                </td>
                                <td><input type="number" min="1" name="components[{{ $i }}][qty]" value="{{ (int) $it->qty }}" class="form-control form-control-sm"></td>
                                <td><input type="number" step="0.01" min="0" name="components[{{ $i }}][unit_price]" value="{{ rtrim(rtrim(number_format((float) $it->unit_price, 2, '.', ''), '0'), '.') }}" class="form-control form-control-sm" @if($it->source === 'inventory') readonly title="{{ __('portal.scope_planner.price_from_inventory') }}" @endif placeholder="0.00"></td>
                                <td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <small class="text-muted d-block mb-1">{{ __('portal.scope_planner.component_price_hint') }}</small>
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
                                        @foreach($services as $svc)<option value="{{ $svc->key }}" data-rate="{{ $svc->unitRate }}" data-desc="{{ $svc->description }}" @selected($it->pricing_rule_id==$svc->key)>{{ $svc->name(app()->getLocale()) }} ({{ number_format($svc->unitRate,0) }})</option>@endforeach
                                    </select>
                                    <input type="text" name="services[{{ $i }}][name]" value="{{ $it->name }}" class="form-control form-control-sm mb-1" placeholder="{{ __('portal.scope_planner.item_name') }}">
                                    <textarea name="services[{{ $i }}][description]" rows="2" class="form-control form-control-sm" placeholder="{{ __('portal.scope_planner.service_description_ph') }}">{{ $it->description }}</textarea>
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
                    <p class="text-muted"><i class="fas fa-circle-info"></i> {{ __('portal.scope_planner.fill_or_generate_hint') }}</p>
                @endif
                <form method="POST" action="{{ route('scope-planner.update', $quote) }}">
                    @csrf @method('PUT')
                    @foreach($flatSections as $key => $type)
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-1">{{ __('scope.'.$key) }}</label>
                            @if($type === 'array')<input type="hidden" name="array_sections[]" value="{{ $key }}">@endif
                            <textarea name="sections[{{ $key }}]" data-autosize rows="{{ $type === 'array' ? 5 : 3 }}" class="form-control" @unless($editable)readonly @endunless placeholder="{{ $type === 'array' ? __('portal.scope_planner.one_per_line') : '' }}">{{ $type === 'array' ? implode("\n", (array) ($c[$key] ?? [])) : ($c[$key] ?? '') }}</textarea>
                        </div>
                    @endforeach

                    {{-- Company: editable per-scope sections (add / remove) --}}
                    @if($quote->customer_category === 'company')
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold m-0">{{ __('portal.scope_planner.scopes') }}</h6>
                            @if($editable)<button type="button" class="btn btn-sm btn-outline-secondary" onclick="addScope()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.add_scope') }}</button>@endif
                        </div>
                        <div id="scopes-wrap">
                            @foreach($quote->scopes as $s)
                                @include('scope-planner._scope_row', ['key' => $s->id, 'scope' => $s, 'editable' => $editable])
                            @endforeach
                        </div>
                    @endif

                    {{-- Indicative timeline (all tiers) — add / remove rows, or full grid --}}
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <h6 class="fw-bold m-0">{{ $quote->label('scope.indicative_timeline', __('scope.indicative_timeline')) }}
                            <span id="adv-badge-timeline" class="badge bg-info ms-1" @if(empty($quote->custom_tables['timeline'] ?? null)) style="display:none" @endif>{{ __('portal.scope_planner.adv_custom_badge') }}</span>
                        </h6>
                        @if($editable)
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addTimelineRow()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.add_row') }}</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAdv('timeline')"><i class="fas fa-table-cells"></i> {{ __('portal.scope_planner.advanced_edit') }}</button>
                        </div>
                        @endif
                    </div>
                    @if(! empty($quote->custom_tables['timeline'] ?? null))
                        <div class="alert alert-info py-1 px-2 small mb-2"><i class="fas fa-circle-info"></i> {{ __('portal.scope_planner.advanced_edit_hint') }}</div>
                    @endif
                    <table class="table table-sm" id="timeline-table"><tbody>
                        @foreach(($c['timeline'] ?? []) as $i => $row)
                        <tr>
                            <td style="width:38%"><input type="text" name="timeline[{{ $i }}][period]" value="{{ $row['period'] ?? ($row['phase'] ?? '') }}" class="form-control form-control-sm" placeholder="{{ __('scope.period') }}" @unless($editable)readonly @endunless></td>
                            <td><input type="text" name="timeline[{{ $i }}][activity]" value="{{ $row['activity'] ?? ($row['notes'] ?? '') }}" class="form-control form-control-sm" placeholder="{{ __('scope.activity') }}" @unless($editable)readonly @endunless></td>
                            <td style="width:36px">@if($editable)<button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button>@endif</td>
                        </tr>
                        @endforeach
                    </tbody></table>

                    {{-- Payment schedule (milestones) — structure editable; amounts computed --}}
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold m-0">{{ $quote->label('scope.payment_schedule', __('scope.payment_schedule')) }}</h6>
                        @if($editable)<button type="button" class="btn btn-sm btn-outline-secondary" onclick="addMilestoneRow()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.add_row') }}</button>@endif
                    </div>
                    <small class="text-muted d-block mb-1">{{ __('portal.scope_planner.milestone_hint') }}</small>
                    <table class="table table-sm" id="milestones-table">
                        <thead><tr><th style="width:64px">{{ __('scope.milestone') }}</th><th>{{ __('scope.trigger') }}</th><th style="width:74px">%</th><th style="width:36px"></th></tr></thead>
                        <tbody>
                        @foreach($quote->milestones as $i => $m)
                        <tr>
                            <td><input type="text" name="milestones[{{ $i }}][code]" value="{{ $m->code }}" class="form-control form-control-sm" @unless($editable)readonly @endunless></td>
                            <td><input type="text" name="milestones[{{ $i }}][trigger]" value="{{ $m->triggerLabel() }}" class="form-control form-control-sm" @unless($editable)readonly @endunless></td>
                            <td><input type="number" step="0.01" min="0" max="100" name="milestones[{{ $i }}][percentage]" value="{{ rtrim(rtrim(number_format($m->percentage,2),'0'),'.') }}" class="form-control form-control-sm ms-pct" oninput="sumPct()" @unless($editable)readonly @endunless></td>
                            <td>@if($editable)<button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove();sumPct()">&times;</button>@endif</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <small class="text-muted">{{ __('portal.scope_planner.pct_sum') }}: <span id="pct-sum">0</span>%</small>

                    {{-- Rename any heading / table column (doc_labels) --}}
                    <hr>
                    <details class="mb-3">
                        <summary class="fw-bold" style="cursor:pointer"><i class="fas fa-tag"></i> {{ __('portal.scope_planner.rename_labels') }}</summary>
                        <small class="text-muted d-block my-2">{{ __('portal.scope_planner.rename_labels_hint') }}</small>
                        <div class="row g-2">
                        @foreach($labelKeys as $lk)
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __($lk) }}</label>
                                <input type="text" name="labels[{{ $lk }}]" value="{{ data_get($quote->doc_labels, $lk, '') }}" placeholder="{{ __($lk) }}" class="form-control form-control-sm" @unless($editable)readonly @endunless>
                            </div>
                        @endforeach
                        </div>
                    </details>

                    @if($editable)
                    {{-- Advanced grid editor state (written by the grid modal). --}}
                    <input type="hidden" name="custom_tables_json" id="custom_tables_json" value="{{ json_encode($quote->custom_tables ?: (object) []) }}">
                    {{-- Templates for JS-added rows (inert; not submitted) --}}
                    <template id="scope-tpl">@include('scope-planner._scope_row', ['key' => '__K__', 'scope' => null, 'editable' => true])</template>
                    <template id="tl-tpl"><tr>
                        <td style="width:38%"><input type="text" name="timeline[__I__][period]" class="form-control form-control-sm" placeholder="{{ __('scope.period') }}"></td>
                        <td><input type="text" name="timeline[__I__][activity]" class="form-control form-control-sm" placeholder="{{ __('scope.activity') }}"></td>
                        <td style="width:36px"><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                    </tr></template>
                    <template id="ms-tpl"><tr>
                        <td><input type="text" name="milestones[__I__][code]" class="form-control form-control-sm" value="M"></td>
                        <td><input type="text" name="milestones[__I__][trigger]" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="milestones[__I__][percentage]" class="form-control form-control-sm ms-pct" value="0" oninput="sumPct()"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove();sumPct()">&times;</button></td>
                    </tr></template>
                    @endif

                    @if($editable)
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.scope_planner.save_sections') }}</button>
                        <a href="{{ route('scope-planner.show', ['quote' => $quote, 'step' => 'items']) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ __('portal.scope_planner.back_to_items') }}</a>
                    </div>
                    @endif
                </form>
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

@if($editable)
{{-- Advanced grid editor modal (Phase 2B) --}}
<div id="advOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1090;padding:24px;overflow:auto;">
    <div style="max-width:920px;margin:0 auto;background:var(--bg-primary,#fff);border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.3);">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h5 class="m-0"><i class="fas fa-table-cells"></i> {{ __('portal.scope_planner.advanced_edit') }}</h5>
            <button type="button" class="btn-close" onclick="advClose()" aria-label="{{ __('portal.scope_planner.adv_cancel') }}"></button>
        </div>
        <div class="p-3">
            <p class="text-muted small">{{ __('portal.scope_planner.advanced_edit_hint') }}</p>
            <div class="d-flex gap-2 mb-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="advAddCol()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.adv_add_col') }}</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="advAddRow()"><i class="fas fa-plus"></i> {{ __('portal.scope_planner.adv_add_row') }}</button>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="advResetCur()"><i class="fas fa-rotate-left"></i> {{ __('portal.scope_planner.adv_reset') }}</button>
            </div>
            <div style="overflow:auto;"><table class="table table-sm table-bordered align-middle mb-0" id="advGrid"></table></div>
        </div>
        <div class="d-flex justify-content-end gap-2 p-3 border-top">
            <button type="button" class="btn btn-outline-secondary" onclick="advClose()">{{ __('portal.scope_planner.adv_cancel') }}</button>
            <button type="button" class="btn btn-primary" onclick="advApply()"><i class="fas fa-check"></i> {{ __('portal.scope_planner.adv_save') }}</button>
        </div>
    </div>
</div>
@endif

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
// JSON-encoded catalog data + DOM construction — never string-interpolate
// catalog names into HTML/JS (names may contain backticks, dollar-brace, or markup).
const STOCK = @json($stockJson);
const SVCS = @json($svcJson);
const L_MANUAL = @json('— '.__('portal.scope_planner.manual').' —');
const L_NAME = @json(__('portal.scope_planner.item_name'));

const L_DESC = @json(__('portal.scope_planner.service_description_ph'));
function mkSelect(name, items, valueKey, cls) {
    const sel = document.createElement('select');
    sel.name = name; sel.className = 'form-select form-select-sm mb-1' + (cls ? ' ' + cls : '');
    const o0 = document.createElement('option'); o0.value = ''; o0.textContent = L_MANUAL; sel.appendChild(o0);
    items.forEach(function (it) {
        const o = document.createElement('option');
        o.value = it[valueKey]; o.textContent = it.label;          // textContent = injection-safe
        if (it.rate !== undefined) o.dataset.rate = it.rate;
        if (it.price !== undefined) o.dataset.price = it.price;
        if (it.desc !== undefined && it.desc !== null) o.dataset.desc = it.desc;
        sel.appendChild(o);
    });
    return sel;
}
function mkInput(name, type, attrs) {
    const i = document.createElement('input');
    i.name = name; i.type = type; i.className = 'form-control form-control-sm';
    Object.assign(i, attrs || {});
    return i;
}
function mkTextarea(name, placeholder) {
    const t = document.createElement('textarea');
    t.name = name; t.rows = 2; t.className = 'form-control form-control-sm';
    if (placeholder) t.placeholder = placeholder;
    return t;
}
function mkRemoveCell() {
    const td = document.createElement('td');
    const b = document.createElement('button');
    b.type = 'button'; b.className = 'btn btn-sm btn-link text-danger'; b.textContent = '×';
    b.addEventListener('click', function () { b.closest('tr').remove(); });
    td.appendChild(b); return td;
}
function addComponentRow() {
    const tr = document.createElement('tr');
    const td1 = document.createElement('td');
    const sel = mkSelect('components[' + compIdx + '][stock_item_id]', STOCK, 'id', 'comp-select');
    sel.addEventListener('change', function () { fillComponentPrice(sel); });
    td1.appendChild(sel);
    td1.appendChild(mkInput('components[' + compIdx + '][name]', 'text', { placeholder: L_NAME }));
    const td2 = document.createElement('td'); td2.appendChild(mkInput('components[' + compIdx + '][qty]', 'number', { min: 1, value: 1 }));
    const td3 = document.createElement('td'); td3.appendChild(mkInput('components[' + compIdx + '][unit_price]', 'number', { step: '0.01', min: 0, value: 0 }));
    tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3); tr.appendChild(mkRemoveCell());
    document.querySelector('#components-table tbody').appendChild(tr); compIdx++;
}
function addServiceRow() {
    const tr = document.createElement('tr');
    const td1 = document.createElement('td');
    const sel = mkSelect('services[' + svcIdx + '][pricing_rule_id]', SVCS, 'key', 'svc-select');
    sel.addEventListener('change', function () { fillRate(sel); });
    td1.appendChild(sel);
    td1.appendChild(mkInput('services[' + svcIdx + '][name]', 'text', { placeholder: L_NAME }));
    td1.appendChild(mkTextarea('services[' + svcIdx + '][description]', L_DESC));
    const td2 = document.createElement('td'); td2.appendChild(mkInput('services[' + svcIdx + '][qty]', 'number', { min: 1, value: 1 }));
    const td3 = document.createElement('td'); td3.appendChild(mkInput('services[' + svcIdx + '][unit_price]', 'number', { step: '0.01', min: 0, value: 0 }));
    tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3); tr.appendChild(mkRemoveCell());
    document.querySelector('#services-table tbody').appendChild(tr); svcIdx++;
}
function fillRate(sel) {
    const opt = sel.options[sel.selectedIndex];
    const row = sel.closest('tr');
    const rate = opt ? opt.getAttribute('data-rate') : null;
    if (rate) { const price = row.querySelector('input[name*="[unit_price]"]'); if (price && (!price.value || price.value === '0')) price.value = rate; }
    // Auto-recall a pre-defined service's stored description when the box is empty.
    const desc = opt ? opt.getAttribute('data-desc') : null;
    if (desc) { const box = row.querySelector('textarea[name*="[description]"]'); if (box && box.value.trim() === '') box.value = desc; }
}
function fillComponentPrice(sel) {
    const opt = sel.options[sel.selectedIndex];
    const row = sel.closest('tr');
    const price = row.querySelector('input[name*="[unit_price]"]');
    if (! price) return;
    if (sel.value === '') {                 // back to Manual → let the user type a price
        price.readOnly = false;
        return;
    }
    const p = opt ? opt.getAttribute('data-price') : null;
    if (p !== null) { price.value = p; price.readOnly = true; }   // inventory → tier price, locked
}
// Auto-grow editable section textareas so the full content is visible.
function autosize(el) { el.style.height = 'auto'; el.style.height = (el.scrollHeight + 4) + 'px'; }
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('textarea[data-autosize]').forEach(function (t) { autosize(t); t.addEventListener('input', function () { autosize(t); }); });
});

// ---- Targeted editor: scope / timeline / milestone repeaters ----
let tlIdx = {{ count($c['timeline'] ?? []) }};
let msIdx = {{ $quote->milestones->count() }};
let scNew = 0;
function addScope() {
    const t = document.getElementById('scope-tpl'); if (!t) return;
    const frag = t.content.cloneNode(true);
    const key = 'new_' + (scNew++);
    frag.querySelectorAll('[name]').forEach(function (el) { el.name = el.name.split('__K__').join(key); });
    const wrap = document.getElementById('scopes-wrap'); if (!wrap) return;
    wrap.appendChild(frag);
    const node = wrap.lastElementChild;
    if (node) node.querySelectorAll('textarea[data-autosize]').forEach(function (t) { autosize(t); t.addEventListener('input', function () { autosize(t); }); });
}
function addRowFrom(tplId, tbodySel, idx) {
    const t = document.getElementById(tplId); if (!t) return;
    const frag = t.content.cloneNode(true);
    frag.querySelectorAll('[name]').forEach(function (el) { el.name = el.name.split('__I__').join(idx); });
    const tb = document.querySelector(tbodySel); if (tb) tb.appendChild(frag);
}
function addTimelineRow() { addRowFrom('tl-tpl', '#timeline-table tbody', tlIdx++); }
function addMilestoneRow() { addRowFrom('ms-tpl', '#milestones-table tbody', msIdx++); sumPct(); }
function sumPct() {
    let s = 0;
    document.querySelectorAll('#milestones-table .ms-pct').forEach(function (i) { s += parseFloat(i.value || 0) || 0; });
    const el = document.getElementById('pct-sum');
    if (el) { el.textContent = (Math.round(s * 100) / 100); el.style.color = Math.abs(s - 100) < 0.01 ? 'var(--success-color,#198754)' : 'var(--danger-color,#dc3545)'; }
}
document.addEventListener('DOMContentLoaded', sumPct);

// ---- Advanced grid editor (Phase 2B): generic table with merge cells ----
const ADV_DEFAULT_COLS = @json(['timeline' => [$quote->label('scope.period', __('scope.period')), $quote->label('scope.activity', __('scope.activity'))]]);
const ADV_T = { unmerge: @json(__('portal.scope_planner.adv_unmerge')), mright: @json(__('portal.scope_planner.adv_merge_right')), mdown: @json(__('portal.scope_planner.adv_merge_down')), up: @json(__('portal.scope_planner.adv_move_up')), down: @json(__('portal.scope_planner.adv_move_down')) };
let advTables = (function () { var el = document.getElementById('custom_tables_json'); if (!el) return {}; try { return JSON.parse(el.value || '{}') || {}; } catch (e) { return {}; } })();
let advCur = null, advGrid = null;

function advCell(t) { return { text: t || '', colspan: 1, rowspan: 1, align: 'start' }; }
function advSeed(slug) {
    const cols = (ADV_DEFAULT_COLS[slug] || ['Column 1', 'Column 2']).map(function (l) { return { label: l, align: 'start' }; });
    let rows = [];
    if (slug === 'timeline') {
        document.querySelectorAll('#timeline-table tbody tr').forEach(function (tr) {
            const ins = tr.querySelectorAll('input');
            if (ins.length >= 2) rows.push([advCell(ins[0].value), advCell(ins[1].value)]);
        });
    }
    if (!rows.length) rows = [cols.map(function () { return advCell(''); })];
    return { columns: cols, rows: rows };
}
function advNormalize(g) {
    const n = g.columns.length;
    g.rows.forEach(function (row) { while (row.length < n) row.push(advCell('')); if (row.length > n) row.length = n; });
}
function advCoverage(g) {
    const cov = {};
    g.rows.forEach(function (row, r) { row.forEach(function (cell, c) {
        const cs = cell.colspan || 1, rs = cell.rowspan || 1;
        if (cs > 1 || rs > 1) { for (let dr = 0; dr < rs; dr++) for (let dc = 0; dc < cs; dc++) { if (dr || dc) cov[(r + dr) + ',' + (c + dc)] = true; } }
    }); });
    return cov;
}
function openAdv(slug) {
    advCur = slug;
    advGrid = advTables[slug] ? JSON.parse(JSON.stringify(advTables[slug])) : advSeed(slug);
    advNormalize(advGrid);
    advRender();
    document.getElementById('advOverlay').style.display = 'block';
}
function advClose() { const o = document.getElementById('advOverlay'); if (o) o.style.display = 'none'; advCur = null; advGrid = null; }
function advAddCol() { advGrid.columns.push({ label: '', align: 'start' }); advGrid.rows.forEach(function (r) { r.push(advCell('')); }); advRender(); }
function advDelCol(c) { if (advGrid.columns.length <= 1) return; advGrid.columns.splice(c, 1); advGrid.rows.forEach(function (r) { r.splice(c, 1); r.forEach(function (cell) { cell.colspan = 1; cell.rowspan = 1; }); }); advRender(); }
function advAddRow() { advGrid.rows.push(advGrid.columns.map(function () { return advCell(''); })); advRender(); }
function advDelRow(r) { if (advGrid.rows.length <= 1) return; advGrid.rows.splice(r, 1); advGrid.rows.forEach(function (row) { row.forEach(function (cell) { cell.rowspan = 1; }); }); advRender(); }
function advMoveRow(r, d) { const j = r + d; if (j < 0 || j >= advGrid.rows.length) return; const t = advGrid.rows[r]; advGrid.rows[r] = advGrid.rows[j]; advGrid.rows[j] = t; advRender(); }
function advMergeRight(r, c) { const cell = advGrid.rows[r][c]; if (c + (cell.colspan || 1) < advGrid.columns.length) { cell.colspan = (cell.colspan || 1) + 1; advRender(); } }
function advMergeDown(r, c) { const cell = advGrid.rows[r][c]; if (r + (cell.rowspan || 1) < advGrid.rows.length) { cell.rowspan = (cell.rowspan || 1) + 1; advRender(); } }
function advUnmerge(r, c) { const cell = advGrid.rows[r][c]; cell.colspan = 1; cell.rowspan = 1; advRender(); }
function advBtn(txt, title, fn) { const b = document.createElement('button'); b.type = 'button'; b.className = 'btn btn-sm btn-outline-secondary py-0 px-1'; b.title = title || ''; b.textContent = txt; b.addEventListener('click', fn); return b; }
function advRender() {
    const g = advGrid, cov = advCoverage(g), t = document.getElementById('advGrid');
    if (!t) return;
    t.innerHTML = '';
    const thead = document.createElement('thead'); const htr = document.createElement('tr');
    g.columns.forEach(function (col, c) {
        const th = document.createElement('th');
        const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'form-control form-control-sm'; inp.value = col.label || ''; inp.placeholder = '#' + (c + 1);
        inp.addEventListener('input', function () { g.columns[c].label = inp.value; });
        const bar = document.createElement('div'); bar.className = 'd-flex gap-1 mt-1';
        const al = document.createElement('select'); al.className = 'form-select form-select-sm';
        ['start', 'center', 'end'].forEach(function (a) { const o = document.createElement('option'); o.value = a; o.textContent = a; if (col.align === a) o.selected = true; al.appendChild(o); });
        al.addEventListener('change', function () { g.columns[c].align = al.value; });
        bar.appendChild(al); bar.appendChild(advBtn('×', '', function () { advDelCol(c); }));
        th.appendChild(inp); th.appendChild(bar); htr.appendChild(th);
    });
    const corner = document.createElement('th'); corner.style.width = '96px'; htr.appendChild(corner);
    thead.appendChild(htr); t.appendChild(thead);
    const tb = document.createElement('tbody');
    g.rows.forEach(function (row, r) {
        const tr = document.createElement('tr');
        row.forEach(function (cell, c) {
            if (cov[r + ',' + c]) return;
            const td = document.createElement('td');
            if ((cell.colspan || 1) > 1) td.colSpan = cell.colspan;
            if ((cell.rowspan || 1) > 1) td.rowSpan = cell.rowspan;
            const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'form-control form-control-sm'; inp.value = cell.text || '';
            inp.addEventListener('input', function () { cell.text = inp.value; });
            td.appendChild(inp);
            const bar = document.createElement('div'); bar.className = 'd-flex gap-1 mt-1';
            if ((cell.colspan || 1) > 1 || (cell.rowspan || 1) > 1) {
                bar.appendChild(advBtn('⤺', ADV_T.unmerge, function () { advUnmerge(r, c); }));
            } else {
                bar.appendChild(advBtn('▶', ADV_T.mright, function () { advMergeRight(r, c); }));
                bar.appendChild(advBtn('▼', ADV_T.mdown, function () { advMergeDown(r, c); }));
            }
            td.appendChild(bar); tr.appendChild(td);
        });
        const ctl = document.createElement('td'); const wrap = document.createElement('div'); wrap.className = 'd-flex gap-1';
        wrap.appendChild(advBtn('▲', ADV_T.up, function () { advMoveRow(r, -1); }));
        wrap.appendChild(advBtn('▼', ADV_T.down, function () { advMoveRow(r, 1); }));
        const del = document.createElement('button'); del.type = 'button'; del.className = 'btn btn-sm btn-link text-danger py-0 px-1'; del.textContent = '×'; del.addEventListener('click', function () { advDelRow(r); });
        wrap.appendChild(del); ctl.appendChild(wrap); tr.appendChild(ctl);
        tb.appendChild(tr);
    });
    t.appendChild(tb);
}
function advWrite() { const el = document.getElementById('custom_tables_json'); if (el) el.value = JSON.stringify(advTables); }
function advApply() {
    if (!advCur || !advGrid) return advClose();
    const cov = advCoverage(advGrid);
    advGrid.rows.forEach(function (row, r) { row.forEach(function (cell, c) { cell.merged = !!cov[r + ',' + c]; }); });
    advTables[advCur] = advGrid; advWrite();
    const badge = document.getElementById('adv-badge-' + advCur); if (badge) badge.style.display = '';
    advClose();
}
function advResetCur() {
    if (advCur && advTables[advCur]) delete advTables[advCur];
    advWrite();
    const badge = document.getElementById('adv-badge-' + advCur); if (badge) badge.style.display = 'none';
    advClose();
}
</script>
@endpush
