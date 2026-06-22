@php $opp = $opportunity ?? null; @endphp
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">{{ __('portal.crm.deal_name') }} *</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $opp?->name) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.stage') }} *</label>
        <select name="stage" class="form-select" required>
            @foreach($stages as $k => $label)
                <option value="{{ $k }}" @selected(old('stage', $opp?->stage) === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.company') }}</label>
        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $opp?->company_name) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.contact') }}</label>
        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $opp?->contact_name) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.email') }}</label>
        <input type="email" name="email" class="form-control" dir="ltr" value="{{ old('email', $opp?->email) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.phone') }}</label>
        <input type="text" name="phone" class="form-control" dir="ltr" value="{{ old('phone', $opp?->phone) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.expected_value') }}</label>
        <input type="number" step="0.01" min="0" name="expected_value" class="form-control" value="{{ old('expected_value', $opp?->expected_value ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.probability') }} (%)</label>
        <input type="number" min="0" max="100" name="probability" class="form-control" value="{{ old('probability', $opp?->probability ?? 10) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.close_date') }}</label>
        <input type="date" name="expected_close_date" class="form-control" value="{{ old('expected_close_date', optional($opp?->expected_close_date)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.source') }}</label>
        <select name="source" class="form-select">
            <option value="">—</option>
            @foreach($sources as $s)
                <option value="{{ $s }}" @selected(old('source', $opp?->source) === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.owner') }}</label>
        <select name="owner_id" class="form-select">
            <option value="">—</option>
            @foreach($owners as $u)
                <option value="{{ $u->id }}" @selected((int) old('owner_id', $opp?->owner_id) === $u->id)>{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('portal.crm.client') }}</label>
        <div class="d-flex gap-2 align-items-start">
            <select name="client_id" id="client_id" class="form-select">
                <option value="">—</option>
                @foreach($clients as $u)
                    <option value="{{ $u->id }}" @selected((int) old('client_id', $opp?->client_id) === $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-primary text-nowrap" onclick="quickAddClient('client_id')" title="{{ __('portal.quick_client.add') }}">
                <i class="fas fa-user-plus"></i>
            </button>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.company') }} <small class="text-muted">({{ __('portal.crm.from_book') }})</small></label>
        <select name="company_id" class="form-select">
            <option value="">—</option>
            @foreach($companies as $co)
                <option value="{{ $co->id }}" @selected((int) old('company_id', $opp?->company_id) === $co->id)>{{ $co->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('portal.crm.contact') }} <small class="text-muted">({{ __('portal.crm.from_book') }})</small></label>
        <select name="contact_id" class="form-select">
            <option value="">—</option>
            @foreach($contacts as $ct)
                <option value="{{ $ct->id }}" @selected((int) old('contact_id', $opp?->contact_id) === $ct->id)>{{ $ct->name }}{{ $ct->company ? ' — '.$ct->company->name : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('portal.crm.description') }}</label>
        <textarea name="description" rows="4" class="form-control">{{ old('description', $opp?->description) }}</textarea>
    </div>
</div>
