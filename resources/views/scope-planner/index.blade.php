@extends('layouts.internal-dashboard')
@section('title', __('portal.scope_ai.title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_ai.title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.scope_ai.subtitle') }}</p>
    </div>
    <a href="{{ route('scope-planner.create') }}" class="btn btn-light"><i class="fas fa-file-signature"></i> {{ __('portal.scope_planner.new_document') }}</a>
</div>

@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<form method="POST" action="{{ route('scope-planner.plan') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><span class="card-title">{{ __('portal.scope_ai.inputs') }}</span></div>
                <div class="card-content">
                    <label class="form-label">{{ __('portal.scope_ai.project_type') }}</label>
                    <input type="text" name="project_type" class="form-control mb-2" required
                           value="{{ $result['project_type'] ?? old('project_type') }}" placeholder="{{ __('portal.scope_ai.project_type_ph') }}">
                    <label class="form-label">{{ __('portal.scope_ai.requirements') }}</label>
                    <textarea name="requirements" rows="5" class="form-control mb-2" required
                              placeholder="{{ __('portal.scope_ai.requirements_ph') }}">{{ $result['requirements'] ?? old('requirements') }}</textarea>
                    <label class="form-label">{{ __('portal.scope_ai.budget') }}</label>
                    <input type="text" name="budget" class="form-control mb-3" value="{{ $result['budget'] ?? old('budget') }}" placeholder="$10,000">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-wand-magic-sparkles"></i> {{ __('portal.scope_ai.draft') }}</button>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="card-title">{{ __('portal.scope_ai.scope') }}</span>
                    @if($result)<span class="badge bg-{{ $result['source']==='gemini' ? 'primary':'secondary' }}">{{ $result['source']==='gemini' ? 'Gemini' : __('portal.scope_ai.offline') }}</span>@endif
                </div>
                <div class="card-content">
                    <textarea name="scope" rows="12" class="form-control" style="font-family:inherit;" placeholder="{{ __('portal.scope_ai.scope_placeholder') }}">{{ $result['scope'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer pricing tier (drives StockItem prices) --}}
    <div class="card mt-3">
        <div class="card-content d-flex flex-wrap align-items-center gap-2">
            <label class="form-label mb-0"><i class="fas fa-tags"></i> {{ __('portal.scope_ai.customer_category') }}</label>
            <select name="customer_category" class="form-select" style="max-width:240px;">
                @foreach($categories as $c)
                    <option value="{{ $c }}" @selected(($result['customer_category'] ?? 'company') === $c)>{{ __('portal.scope_ai.cat.'.$c) }}</option>
                @endforeach
            </select>
            <small class="text-muted">{{ __('portal.scope_ai.customer_category_hint') }}</small>
        </div>
    </div>

    {{-- Inventory picker --}}
    <div class="card mt-3">
        <div class="card-header"><span class="card-title">{{ __('portal.scope_ai.inventory') }}</span></div>
        <div class="card-content">
            <p class="text-muted" style="font-size:.85rem;">{{ __('portal.scope_ai.inventory_hint') }}</p>
            @foreach($inventory as $category => $items)
                <div class="mb-2"><strong style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-500);">{{ $category }}</strong></div>
                <div class="row g-2 mb-3">
                    @foreach($items as $item)
                        @php $checked = $result && in_array($item->id, $result['selectedIds'] ?? []); @endphp
                        <div class="col-md-6">
                            <label class="d-flex align-items-center gap-2 p-2" style="border:1px solid var(--gray-200); border-radius:8px;">
                                <input type="checkbox" name="items[]" value="{{ $item->id }}" @checked($checked)>
                                <span style="flex:1;">{{ $item->name }}</span>
                                <input type="number" name="qty[{{ $item->id }}]" value="1" min="1" class="form-control" style="width:64px; height:32px; padding:2px 6px;">
                            </label>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Stock inventory picker (labs + tiered pricing) --}}
    <div class="card mt-3">
        <div class="card-header"><span class="card-title"><i class="fas fa-boxes-stacked"></i> {{ __('portal.scope_ai.stock_inventory') }}</span></div>
        <div class="card-content">
            <p class="text-muted" style="font-size:.85rem;">{{ __('portal.scope_ai.stock_inventory_hint') }}</p>
            @forelse($stockInventory as $category => $items)
                <div class="mb-2"><strong style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-500);">{{ $category ?: __('portal.scope_ai.uncategorised') }}</strong></div>
                <div class="row g-2 mb-3">
                    @foreach($items as $item)
                        @php $checked = $result && in_array($item->id, $result['selectedStockIds'] ?? []); @endphp
                        <div class="col-md-6">
                            <label class="d-flex align-items-center gap-2 p-2" style="border:1px solid var(--gray-200); border-radius:8px;">
                                <input type="checkbox" name="stock_items[]" value="{{ $item->id }}" @checked($checked)>
                                <span style="flex:1;">{{ $item->name }} <small class="text-muted">({{ $item->product_id }})</small></span>
                                <input type="number" name="stock_qty[{{ $item->id }}]" value="1" min="1" class="form-control" style="width:64px; height:32px; padding:2px 6px;">
                            </label>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="text-muted">{{ __('portal.scope_ai.no_stock') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Actions --}}
    <div class="card mt-3"><div class="card-content">
        <div class="d-flex flex-wrap align-items-end gap-2">
            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-calculator"></i> {{ __('portal.scope_ai.build_quote') }}</button>
            <div style="min-width:240px;">
                <label class="form-label" style="font-size:.78rem;">{{ __('portal.scope_ai.link_opportunity') }}</label>
                <select name="opportunity_id" class="form-select">
                    <option value="">{{ __('portal.scope_ai.no_opportunity') }}</option>
                    @foreach($opportunities as $op)
                        <option value="{{ $op->id }}">{{ $op->name }}{{ $op->company_name ? ' — '.$op->company_name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" formaction="{{ route('scope-planner.save-quote') }}" class="btn btn-primary"><i class="fas fa-file-invoice"></i> {{ __('portal.scope_ai.save_quote') }}</button>
        </div>
    </div></div>
</form>

@if($result && count($result['lines']))
<div class="row g-3 mt-1">
    {{-- Internal detailed --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-lock"></i> {{ __('portal.scope_ai.internal_view') }}</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('portal.scope_ai.item') }}</th><th>{{ __('portal.scope_ai.cost') }}</th><th>{{ __('portal.scope_ai.markup') }}</th><th class="text-end">{{ __('portal.scope_ai.qty') }}</th><th class="text-end">{{ __('portal.scope_ai.price') }}</th></tr></thead>
                    <tbody>
                        @foreach($result['lines'] as $l)
                            <tr>
                                <td>{{ $l['name'] }} <span class="badge bg-secondary">{{ $l['category'] }}</span></td>
                                <td>${{ number_format((float) $l['cost'], 2) }}</td>
                                <td>{{ rtrim(rtrim(number_format((float) $l['markup'], 2), '0'), '.') }}%</td>
                                <td class="text-end">{{ $l['qty'] }}</td>
                                <td class="text-end">${{ number_format($l['line_client'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="4" class="text-end"><strong>{{ __('portal.scope_ai.internal_cost_total') }}</strong></td><td class="text-end">${{ number_format($result['internalTotal'], 2) }}</td></tr>
                        <tr><td colspan="4" class="text-end"><strong>{{ __('portal.scope_ai.margin') }}</strong></td><td class="text-end" style="color:var(--success-color);"><strong>${{ number_format($result['margin'], 2) }}</strong></td></tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-content"><small class="text-muted"><i class="fas fa-triangle-exclamation"></i> {{ __('portal.scope_ai.confidential') }}</small></div>
        </div>
    </div>

    {{-- Client grouped --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-user"></i> {{ __('portal.scope_ai.client_view') }}</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('portal.scope_ai.category') }}</th><th class="text-end">{{ __('portal.scope_ai.amount') }}</th></tr></thead>
                    <tbody>
                        @foreach($result['clientGrouped'] as $cat => $amount)
                            <tr><td>{{ $cat }}</td><td class="text-end">${{ number_format($amount, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td><strong>{{ __('portal.scope_ai.total') }}</strong></td><td class="text-end"><strong>${{ number_format($result['clientTotal'], 2) }}</strong></td></tr></tfoot>
                </table>
            </div>
            <div class="card-content"><small class="text-muted">{{ __('portal.scope_ai.client_note') }}</small></div>
        </div>
    </div>
</div>
@endif
@endsection
