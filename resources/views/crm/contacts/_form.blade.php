@php $ct = $contact ?? null; $pre = $preCompany ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.contact') }} *</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $ct?->name) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.company') }}</label>
        <select name="company_id" class="form-select">
            <option value="">—</option>
            @foreach($companies as $co)<option value="{{ $co->id }}" @selected((int) old('company_id', $ct?->company_id ?? $pre) === $co->id)>{{ $co->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.job_title') }}</label>
        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $ct?->job_title) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('portal.crm.email') }}</label>
        <input type="email" name="email" class="form-control" dir="ltr" value="{{ old('email', $ct?->email) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('portal.crm.phone') }}</label>
        <input type="text" name="phone" class="form-control" dir="ltr" value="{{ old('phone', $ct?->phone) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.owner') }}</label>
        <select name="owner_id" class="form-select">
            <option value="">—</option>
            @foreach($owners as $u)<option value="{{ $u->id }}" @selected((int) old('owner_id', $ct?->owner_id) === $u->id)>{{ $u->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.tags') }}</label>
        <input type="text" name="tags" class="form-control" value="{{ old('tags', $ct ? $ct->tagList() : '') }}" placeholder="{{ __('portal.crm.tags_hint') }}">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_primary" value="0">
            <input type="checkbox" name="is_primary" value="1" class="form-check-input" id="isPrimary" @checked(old('is_primary', $ct?->is_primary))>
            <label class="form-check-label" for="isPrimary">{{ __('portal.crm.is_primary') }}</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('portal.crm.notes') }}</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $ct?->notes) }}</textarea>
    </div>
</div>
