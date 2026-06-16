@extends('layouts.internal-dashboard')
@section('title', __('portal.research.manager.show.page_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('research.manager.index') }}">{{ __('portal.research.manager.show.breadcrumb_parent') }}</a></li>
<li class="breadcrumb-item active">#{{ $research->id }} - {{ Str::limit($research->title, 30) }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $research->title }}</h3>
                <span class="status-badge {{ $research->getStatusBadgeColor() }}">{{ $research->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="mb-3"><strong>{{ __('portal.research.manager.show.description') }}</strong><p>{{ $research->description }}</p></div>
                @if($research->requirements)
                <div class="mb-3"><strong>{{ __('portal.research.manager.show.requirements') }}</strong><p>{{ $research->requirements }}</p></div>
                @endif
                @if($research->nda_signed_at)
                <div class="mb-3"><strong>{{ __('portal.research.manager.show.nda_signed') }}</strong> {{ $research->nda_signed_at->translatedFormat('M d, Y H:i') }}</div>
                @endif
                @if($research->sla_signed_at)
                <div class="mb-3"><strong>{{ __('portal.research.manager.show.sla_signed') }}</strong> {{ $research->sla_signed_at->translatedFormat('M d, Y H:i') }}</div>
                @endif
                @if($research->meeting_scheduled_at)
                <div class="mb-3"><strong>{{ __('portal.research.manager.show.meeting_scheduled') }}</strong> {{ $research->meeting_scheduled_at->translatedFormat('M d, Y H:i') }}</div>
                @endif
                @if($research->meeting_link)
                <div class="mb-3"><strong>{{ __('portal.research.manager.show.meeting_link') }}</strong> <a href="{{ $research->meeting_link }}" target="_blank">{{ $research->meeting_link }}</a></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.research.manager.show.client_info') }}</h3></div>
            <div class="card-content">
                <p><strong>{{ __('portal.research.manager.show.name') }}</strong> {{ $research->user->name }}</p>
                <p><strong>{{ __('portal.research.manager.show.email') }}</strong> <a href="mailto:{{ $research->user->email }}">{{ $research->user->email }}</a></p>
                @if($research->user->phone)<p><strong>{{ __('portal.research.manager.show.phone') }}</strong> <a href="tel:{{ $research->user->phone }}">{{ $research->user->phone }}</a></p>@endif
                <p><strong>{{ __('portal.research.manager.show.submitted') }}</strong> {{ $research->created_at->translatedFormat('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.research.manager.show.actions') }}</h3></div>
            <div class="card-content">
                @if($research->isNdaSigned() && !$research->assigned_to)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> {{ __('portal.research.manager.show.assign_employee') }}</button>
                @endif
                @if($research->isInProgress())
                <button class="btn btn-success btn-block mb-2" onclick="markComplete()"><i class="fas fa-check"></i> {{ __('portal.research.manager.show.mark_complete') }}</button>
                @endif
                @if($research->assignedTo)
                <p><strong>{{ __('portal.research.manager.show.assigned_to_label') }}</strong> {{ $research->assignedTo->name }}</p>
                @endif
                @if($research->status === 'completed')
                    @php $convertedProject = $research->convertedProject(); @endphp
                    @if($convertedProject)
                    <a href="{{ route('projects.manager.show', $convertedProject) }}" class="btn btn-outline-primary btn-block mb-2"><i class="fas fa-diagram-project"></i> {{ __('portal.services.view_project') }}</a>
                    @else
                    <form method="POST" action="{{ route('research.convert-to-project', $research) }}">@csrf
                        <button class="btn btn-primary btn-block mb-2"><i class="fas fa-diagram-project"></i> {{ __('portal.services.convert_to_project') }}</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.research.manager.show.modal_assign_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('research.assign', $research) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">{{ __('portal.research.manager.show.select_employee') }}</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __('portal.research.manager.show.assign_assign') }}</button></div></form></div></div></div>

<div class="modal fade" id="completeModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.research.manager.show.mark_complete') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('research.complete', $research) }}">@csrf<div class="modal-body"><label class="form-label fw-bold">{{ __('portal.research.manager.show.findings_label') }}</label><textarea name="research_findings" rows="6" class="form-control" required placeholder="{{ __('portal.research.manager.show.findings_placeholder') }}">{{ old('research_findings', $research->research_findings) }}</textarea></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.research.manager.show.cancel') }}</button><button type="submit" class="btn btn-success"><i class="fas fa-check"></i> {{ __('portal.research.manager.show.mark_complete') }}</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>
function showAssignModal(){new bootstrap.Modal(document.getElementById('assignModal')).show();}
function markComplete(){new bootstrap.Modal(document.getElementById('completeModal')).show();}
</script>
@endpush
