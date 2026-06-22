@extends('layouts.internal-dashboard')
@section('title', __('portal.projects_propose.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">{{ __('portal.projects_manager.projects') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.projects_propose.breadcrumb') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lightbulb"></i> {{ __('portal.projects_propose.heading') }}</h3>
            </div>
            <div class="card-content">
                <p class="text-muted">{{ __('portal.projects_propose.intro') }}</p>

                <form method="POST" action="{{ route('projects.propose.store') }}">
                    @csrf

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.project_title') }} *</label>
                        <input type="text" name="title" class="form-control" required value="{{ old('title') }}" placeholder="{{ __('portal.projects_propose.project_title_placeholder') }}">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.description') }} *</label>
                        <textarea name="description" rows="4" class="form-control" required placeholder="{{ __('portal.projects_propose.description_placeholder') }}">{{ old('description') }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.justification') }}</label>
                        <textarea name="proposal_notes" rows="3" class="form-control" placeholder="{{ __('portal.projects_propose.justification_placeholder') }}">{{ old('proposal_notes') }}</textarea>
                        <small class="text-muted">{{ __('portal.projects_propose.justification_hint') }}</small>
                        @error('proposal_notes')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.scope') }}</label>
                        <textarea name="scope" rows="3" class="form-control" placeholder="{{ __('portal.projects_propose.scope_placeholder') }}">{{ old('scope') }}</textarea>
                        @error('scope')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.projects_propose.client') }}</label>
                        <select name="client_id" id="client_id" class="form-control" onchange="onClientPick()">
                            <option value="">{{ __('portal.projects_propose.client_optional') }}</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id')==$client->id)>{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('portal.projects_propose.client_hint') }}</small>
                        @error('client_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    {{-- Unregistered client: capture details, then either record only or invite. --}}
                    <div class="card" id="newClientCard" style="background:rgba(15,150,156,.06);border:1px dashed #0F969C;margin-bottom:1rem;">
                        <div class="card-content">
                            <h5 style="font-size:1rem;margin-bottom:.25rem;"><i class="fas fa-user-plus"></i> {{ __('portal.projects_propose.new_client_heading') }}</h5>
                            <p class="text-muted" style="font-size:.85rem;">{{ __('portal.projects_propose.new_client_hint') }}</p>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>{{ __('portal.quick_client.name') }}</label>
                                    <input type="text" name="new_client_name" class="form-control" value="{{ old('new_client_name') }}">
                                    @error('new_client_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ __('portal.quick_client.email') }}</label>
                                    <input type="email" name="new_client_email" class="form-control" value="{{ old('new_client_email') }}">
                                    @error('new_client_email')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ __('portal.quick_client.phone') }}</label>
                                    <input type="text" name="new_client_phone" class="form-control" value="{{ old('new_client_phone') }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ __('portal.quick_client.company') }}</label>
                                    <input type="text" name="new_client_company" class="form-control" value="{{ old('new_client_company') }}">
                                </div>
                            </div>
                            <small class="text-muted"><i class="fas fa-circle-info"></i> {{ __('portal.projects_propose.new_client_actions_hint') }}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.projects_propose.budget') }}</label>
                                <input type="number" name="budget" class="form-control" step="0.01" min="0" value="{{ old('budget') }}" placeholder="0.00">
                                @error('budget')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.projects_propose.start_date') }}</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                                @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('portal.projects_propose.end_date') }}</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" style="font-size:.9rem;">
                        <i class="fas fa-circle-info"></i> {{ __('portal.projects_propose.review_note') }}
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="record" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> {{ __('portal.projects_propose.submit_record') }}
                        </button>
                        <button type="submit" name="action" value="invite" class="btn btn-success" id="inviteBtn">
                            <i class="fas fa-envelope"></i> {{ __('portal.projects_propose.submit_invite') }}
                        </button>
                        <a href="{{ route('projects.proposals.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
// When an existing client is picked from the list, the "new client" section and
// the Invite button no longer apply — dim them so the choice is unambiguous.
function onClientPick() {
    var picked = !!document.getElementById('client_id').value;
    var card = document.getElementById('newClientCard');
    var inviteBtn = document.getElementById('inviteBtn');
    if (card) { card.style.opacity = picked ? '.5' : '1'; }
    card.querySelectorAll('input').forEach(function (el) { el.disabled = picked; });
    if (inviteBtn) { inviteBtn.disabled = picked; }
}
document.addEventListener('DOMContentLoaded', onClientPick);
</script>
@endpush
@endsection
