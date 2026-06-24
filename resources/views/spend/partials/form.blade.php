@php
    $currency = config('scope.currency', 'SAR');
    $isEdit = isset($spend) && $spend;
    // Initial line items: old() input wins (validation bounce), then the existing
    // request (edit), else one empty row.
    $initial = old('items');
    if (! $initial) {
        $initial = $isEdit
            ? $spend->items->map(fn ($i) => ['description' => $i->description, 'quantity' => $i->quantity, 'unit_amount' => $i->unit_amount, 'product_url' => $i->product_url])->all()
            : [];
    }
    if (empty($initial)) {
        $initial = [['description' => '', 'quantity' => 1, 'unit_amount' => '', 'product_url' => '']];
    }
    $curType = old('type', $isEdit ? $spend->type : 'reimbursement');
    $curScope = old('scope', $isEdit ? $spend->scope : 'project');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="card">
    @csrf
    @if($isEdit)@method('PUT')@endif
    <div class="card-content">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('portal.spend.type_label') }} *</label>
                <select name="type" id="sp_type" class="form-select" onchange="spToggle()">
                    <option value="reimbursement" @selected($curType==='reimbursement')>{{ __('portal.spend.type.reimbursement') }}</option>
                    <option value="purchase" @selected($curType==='purchase')>{{ __('portal.spend.type.purchase') }}</option>
                </select>
                <small class="text-muted" id="sp_type_hint"></small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('portal.spend.scope_label') }} *</label>
                <select name="scope" id="sp_scope" class="form-select" onchange="spToggle()">
                    <option value="project" @selected($curScope==='project')>{{ __('portal.spend.scope.project') }}</option>
                    <option value="general" @selected($curScope==='general')>{{ __('portal.spend.scope.general') }}</option>
                </select>
            </div>

            <div class="col-md-6 sp-project">
                <label class="form-label fw-bold">{{ __('portal.spend.project') }} *</label>
                <select name="project_id" class="form-select">
                    <option value="">{{ __('portal.spend.choose_project') }}</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(old('project_id', $isEdit ? $spend->project_id : null)==$p->id)>{{ $p->title }}</option>
                    @endforeach
                </select>
                @if($projects->isEmpty())<small class="text-warning">{{ __('portal.spend.no_projects_hint') }}</small>@endif
            </div>
            <div class="col-md-6 sp-general">
                <label class="form-label fw-bold">{{ __('portal.spend.category') }}</label>
                <select name="category" class="form-select">
                    <option value="">{{ __('portal.spend.choose_category') }}</option>
                    @foreach($categories as $c)
                        <option value="{{ $c }}" @selected(old('category', $isEdit ? $spend->category : null)===$c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.spend.col_title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $isEdit ? $spend->title : '') }}" required maxlength="255" placeholder="{{ __('portal.spend.title_ph') }}">
            </div>

            {{-- ============ Line items ============ --}}
            <div class="col-12">
                <label class="form-label fw-bold d-block">{{ __('portal.spend.items') }} *</label>
                <p class="text-muted" style="font-size:.82rem;">{{ __('portal.spend.items_hint') }}</p>

                <div class="d-none d-md-flex row g-2 text-muted" style="font-size:.75rem;font-weight:600;">
                    <div class="col-md-4">{{ __('portal.spend.item_desc') }}</div>
                    <div class="col-md-2">{{ __('portal.spend.quantity') }}</div>
                    <div class="col-md-2">{{ __('portal.spend.unit_amount') }} ({{ $currency }})</div>
                    <div class="col-md-3">{{ __('portal.spend.item_link') }}</div>
                    <div class="col-md-1"></div>
                </div>

                <div id="sp-items">
                    @foreach($initial as $i => $row)
                    <div class="sp-item row g-2 align-items-center mb-2">
                        <div class="col-md-4"><input type="text" name="items[{{ $i }}][description]" class="form-control" required maxlength="255" value="{{ $row['description'] ?? '' }}" placeholder="{{ __('portal.spend.item_desc') }}"></div>
                        <div class="col-md-2"><input type="number" min="1" name="items[{{ $i }}][quantity]" class="form-control sp-qty" value="{{ $row['quantity'] ?? 1 }}" placeholder="1"></div>
                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_amount]" class="form-control sp-unit" required value="{{ $row['unit_amount'] ?? '' }}" placeholder="0.00"></div>
                        <div class="col-md-3"><input type="url" name="items[{{ $i }}][product_url]" class="form-control" value="{{ $row['product_url'] ?? '' }}" placeholder="https://… ({{ __('portal.spend.optional') }})"></div>
                        <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger sp-rm" title="{{ __('portal.spend.remove') }}"><i class="fas fa-times"></i></button></div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center mt-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="sp-add"><i class="fas fa-plus"></i> {{ __('portal.spend.add_item') }}</button>
                    <div class="fw-bold">{{ __('portal.spend.total') }}: <span id="sp-total">0.00</span> {{ $currency }}</div>
                </div>
            </div>

            {{-- ============ Receipts / attachments ============ --}}
            @if($isEdit && $spend->receipts->isNotEmpty())
            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.spend.existing_files') }}</label>
                <div class="d-flex flex-column gap-1">
                    @foreach($spend->receipts as $f)
                    <label class="d-flex align-items-center gap-2" style="font-size:.85rem;">
                        <input type="checkbox" name="remove_files[]" value="{{ $f->id }}">
                        <span class="text-danger">{{ __('portal.spend.remove') }}</span>
                        <a href="{{ route('spend.file', $f) }}"><i class="fas fa-paperclip"></i> {{ $f->displayName() }}</a>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.spend.receipts') }}</label>
                <input type="file" name="receipts[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
                <small class="text-muted">{{ __('portal.spend.receipts_hint') }}</small>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.spend.description') }}</label>
                <textarea name="description" rows="3" class="form-control" maxlength="5000" placeholder="{{ __('portal.spend.description_ph') }}">{{ old('description', $isEdit ? $spend->description : '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ $isEdit ? route('spend.show', $spend) : route('spend.index') }}" class="btn btn-secondary">{{ __('portal.spend.cancel') }}</a>
        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ $isEdit ? __('portal.spend.save') : __('portal.spend.submit') }}</button>
    </div>
</form>

<template id="sp-item-tpl">
    <div class="sp-item row g-2 align-items-center mb-2">
        <div class="col-md-4"><input type="text" name="items[__IDX__][description]" class="form-control" required maxlength="255" placeholder="{{ __('portal.spend.item_desc') }}"></div>
        <div class="col-md-2"><input type="number" min="1" name="items[__IDX__][quantity]" class="form-control sp-qty" value="1" placeholder="1"></div>
        <div class="col-md-2"><input type="number" step="0.01" min="0" name="items[__IDX__][unit_amount]" class="form-control sp-unit" required placeholder="0.00"></div>
        <div class="col-md-3"><input type="url" name="items[__IDX__][product_url]" class="form-control" placeholder="https://… ({{ __('portal.spend.optional') }})"></div>
        <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger sp-rm"><i class="fas fa-times"></i></button></div>
    </div>
</template>

<script>
(function () {
    // Seed from the highest existing key+1 (old() rows can be non-contiguous after a
    // remove-then-bounce), so an added row never collides with a rendered index.
    var idx = {{ empty($initial) ? 0 : max(array_keys($initial)) + 1 }};
    var box = document.getElementById('sp-items');
    var tpl = document.getElementById('sp-item-tpl').innerHTML;

    function recalc() {
        var total = 0;
        box.querySelectorAll('.sp-item').forEach(function (row) {
            var q = parseFloat(row.querySelector('.sp-qty')?.value || '1') || 0;
            var u = parseFloat(row.querySelector('.sp-unit')?.value || '0') || 0;
            total += q * u;
        });
        document.getElementById('sp-total').textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    box.addEventListener('input', recalc);
    box.addEventListener('click', function (e) {
        var rm = e.target.closest('.sp-rm');
        if (rm) {
            if (box.querySelectorAll('.sp-item').length > 1) { rm.closest('.sp-item').remove(); recalc(); }
        }
    });
    document.getElementById('sp-add').addEventListener('click', function () {
        box.insertAdjacentHTML('beforeend', tpl.replace(/__IDX__/g, idx++));
        recalc();
    });

    window.spToggle = function () {
        var type = document.getElementById('sp_type').value;
        var scope = document.getElementById('sp_scope').value;
        document.querySelectorAll('.sp-project').forEach(function (e) { e.style.display = scope === 'project' ? '' : 'none'; });
        document.querySelectorAll('.sp-general').forEach(function (e) { e.style.display = scope === 'general' ? '' : 'none'; });
        var hint = document.getElementById('sp_type_hint');
        if (hint) hint.textContent = type === 'purchase' ? @json(__('portal.spend.type_hint_purchase')) : @json(__('portal.spend.type_hint_reimbursement'));
    };
    document.addEventListener('DOMContentLoaded', function () { spToggle(); recalc(); });
    recalc();
})();
</script>
