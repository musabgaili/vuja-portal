@php $c = $company ?? null; @endphp
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">{{ __('portal.crm.company') }} *</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $c?->name) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.industry') }}</label>
        <input type="text" name="industry" class="form-control" value="{{ old('industry', $c?->industry) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.website') }}</label>
        <input type="text" name="website" class="form-control" dir="ltr" value="{{ old('website', $c?->website) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('portal.crm.email') }}</label>
        <input type="email" name="email" class="form-control" dir="ltr" value="{{ old('email', $c?->email) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('portal.crm.phone') }}</label>
        <input type="text" name="phone" class="form-control" dir="ltr" value="{{ old('phone', $c?->phone) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.owner') }}</label>
        <select name="owner_id" class="form-select">
            <option value="">—</option>
            @foreach($owners as $u)<option value="{{ $u->id }}" @selected((int) old('owner_id', $c?->owner_id) === $u->id)>{{ $u->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.tags') }}</label>
        <input type="text" name="tags" class="form-control" value="{{ old('tags', $c ? $c->tagList() : '') }}" placeholder="{{ __('portal.crm.tags_hint') }}">
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('portal.crm.address') }}</label>
        <textarea name="address" rows="2" class="form-control">{{ old('address', $c?->address) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('portal.crm.notes') }}</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $c?->notes) }}</textarea>
    </div>
</div>
