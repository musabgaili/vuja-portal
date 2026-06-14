@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.product_id') }} *</label>
        <input type="text" name="product_id" class="form-control" value="{{ old('product_id', $item->product_id ?? '') }}" required>
    </div>
    <div class="col-md-5">
        <label class="form-label">{{ __('portal.inventory.name') }} *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('portal.inventory.unit') }}</label>
        <input type="text" name="unit" class="form-control" value="{{ old('unit', $item->unit ?? '') }}" placeholder="{{ __('portal.inventory.unit_ph') }}">
    </div>

    <div class="col-md-12">
        <label class="form-label">{{ __('portal.inventory.description') }}</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $item->description ?? '') }}</textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.category') }}</label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $item->category ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.purchase_price') }} *</label>
        <input type="number" step="0.01" min="0" name="purchase_price" class="form-control" value="{{ old('purchase_price', $item->purchase_price ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.product_image') }}</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if (! empty($item) && $item->image_url)
            <img src="{{ $item->image_url }}" alt="" class="mt-2" style="width:64px;height:64px;object-fit:cover;border-radius:6px">
        @endif
    </div>
</div>

<hr>
<h6>{{ __('portal.inventory.selling_prices') }}</h6>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.student') }} *</label>
        <input type="number" step="0.01" min="0" name="price_student" class="form-control" value="{{ old('price_student', $item->price_student ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.entrepreneur') }} *</label>
        <input type="number" step="0.01" min="0" name="price_entrepreneur" class="form-control" value="{{ old('price_entrepreneur', $item->price_entrepreneur ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.inventory.company') }} *</label>
        <input type="number" step="0.01" min="0" name="price_company" class="form-control" value="{{ old('price_company', $item->price_company ?? 0) }}" required>
    </div>
</div>

<hr>
<h6>{{ __('portal.inventory.stock_by_lab') }}</h6>
<div class="row g-3">
    @foreach ($labs as $lab)
        @php $current = isset($item) ? optional($item->labs->firstWhere('id', $lab->id))->pivot->quantity ?? 0 : 0; @endphp
        <div class="col-md-4">
            <label class="form-label">{{ $lab->name }}</label>
            <input type="number" min="0" name="quantities[{{ $lab->id }}]" class="form-control" value="{{ old('quantities.'.$lab->id, $current) }}">
        </div>
    @endforeach
</div>

<div class="form-check mt-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
    <label class="form-check-label" for="is_active">{{ __('portal.inventory.active') }}</label>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.inventory.save_item') }}</button>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">{{ __('portal.team.cancel') }}</a>
</div>
