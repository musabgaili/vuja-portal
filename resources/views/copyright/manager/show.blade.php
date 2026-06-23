@extends('layouts.internal-dashboard')
@section('title', __('portal.copyright.manager.show.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('copyright.manager.index') }}">{{ __('portal.copyright.manager.index.breadcrumb') }}</a></li>
<li class="breadcrumb-item active">#{{ $copyright->id }} - {{ Str::limit($copyright->title, 30) }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $copyright->title }}</h3>
                <span class="status-badge {{ $copyright->getStatusBadgeColor() }}">{{ $copyright->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="mb-3"><strong>{{ __('portal.copyright.manager.show.work_type') }}:</strong> <span class="badge bg-primary">{{ $copyright->workTypeLabel() }}</span></div>
                <div class="mb-3"><strong>{{ __('portal.copyright.manager.show.description') }}:</strong><p>{{ $copyright->description }}</p></div>
                @if($copyright->meeting_requested_at)
                <div class="mb-3"><strong>{{ __('portal.copyright.manager.show.meeting_requested') }}:</strong> {{ $copyright->meeting_requested_at->translatedFormat('M d, Y H:i') }}</div>
                @endif
                @if($copyright->meeting_confirmed_at)
                <div class="mb-3"><strong>{{ __('portal.copyright.manager.show.meeting_confirmed') }}:</strong> {{ $copyright->meeting_confirmed_at->translatedFormat('M d, Y H:i') }}</div>
                @endif
                @if($copyright->registration_number)
                <div class="mb-3"><strong>{{ __('portal.ip.registration_number') }}:</strong> <span class="text-success">{{ $copyright->registration_number }}</span></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.ip.client_information') }}</h3></div>
            <div class="card-content">
                <p><strong>{{ __('portal.ip.name') }}:</strong> {{ $copyright->user->name }}</p>
                <p><strong>{{ __('portal.ip.email') }}:</strong> <a href="mailto:{{ $copyright->user->email }}">{{ $copyright->user->email }}</a></p>
                @if($copyright->user->phone)<p><strong>{{ __('portal.ip.phone') }}:</strong> <a href="tel:{{ $copyright->user->phone }}">{{ $copyright->user->phone }}</a></p>@endif
                <p><strong>{{ __('portal.copyright.manager.show.submitted') }}:</strong> {{ $copyright->created_at->translatedFormat('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>{{ __('portal.permissions.actions') }}</h3></div>
            <div class="card-content">
                @if(!$copyright->assignedTo)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> {{ __('portal.copyright.manager.show.assign_employee') }}</button>
                @else
                <p><strong>{{ __('portal.copyright.manager.show.assigned_to') }}:</strong> {{ $copyright->assignedTo->name }}</p>
                @endif
                @if($copyright->isMeetingBooked() && !$copyright->meeting_confirmed_at)
                <button class="btn btn-success btn-block mb-2" onclick="showConfirmModal()"><i class="fas fa-check"></i> {{ __('portal.ip.confirm_meeting') }}</button>
                @endif
                @if($copyright->isMeetingConfirmed())
                <button class="btn btn-primary btn-block mb-2" onclick="showStatusModal()"><i class="fas fa-edit"></i> {{ __('portal.ip.update_status') }}</button>
                @endif
                @if(in_array($copyright->status, ['registered', 'completed'], true))
                    @php $convertedProject = $copyright->convertedProject(); @endphp
                    @if($convertedProject)
                    <a href="{{ route('projects.manager.show', $convertedProject) }}" class="btn btn-outline-primary btn-block mb-2"><i class="fas fa-diagram-project"></i> {{ __('portal.services.view_project') }}</a>
                    @else
                    <form method="POST" action="{{ route('copyright.convert-to-project', $copyright) }}">@csrf
                        <button class="btn btn-primary btn-block mb-2"><i class="fas fa-diagram-project"></i> {{ __('portal.services.convert_to_project') }}</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@include('partials.service-work-panel', ['model' => $copyright, 'type' => 'copyright'])

<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.copyright.manager.show.assign_employee') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('copyright.assign', $copyright) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">{{ __('portal.copyright.manager.show.select_employee') }}</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __('portal.copyright.manager.show.assign') }}</button></div></form></div></div></div>
<div class="modal fade" id="confirmModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.ip.confirm_meeting') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('copyright.confirm-meeting', $copyright) }}">@csrf<div class="modal-body"><input type="url" name="meeting_link" class="form-control" placeholder="{{ __('portal.ip.meeting_link_optional') }}"></div><div class="modal-footer"><button type="submit" class="btn btn-success">{{ __('portal.ip.confirm') }}</button></div></form></div></div></div>
<div class="modal fade" id="statusModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.ip.update_status') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('copyright.update-status', $copyright) }}">@csrf<div class="modal-body"><select name="status" class="form-control mb-2"><option value="filing">{{ __('portal.ip.status_filing') }}</option><option value="registered">{{ __('portal.ip.status_registered') }}</option><option value="completed">{{ __('portal.ip.status_completed') }}</option></select><input type="text" name="registration_number" class="form-control" placeholder="{{ __('portal.ip.registration_number_if_registered') }}"></div><div class="modal-footer"><button type="submit" class="btn btn-primary">{{ __('portal.ip.update') }}</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>
function showAssignModal(){new bootstrap.Modal(document.getElementById('assignModal')).show();}
function showConfirmModal(){new bootstrap.Modal(document.getElementById('confirmModal')).show();}
function showStatusModal(){new bootstrap.Modal(document.getElementById('statusModal')).show();}
</script>
@endpush
 
