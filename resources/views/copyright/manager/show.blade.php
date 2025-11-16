@extends('layouts.internal-dashboard')
@section('title', 'Copyright Registration Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('copyright.manager.index') }}">Copyright</a></li>
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
                <div class="mb-3"><strong>Work Type:</strong> <span class="badge bg-primary">{{ $copyright->work_type }}</span></div>
                <div class="mb-3"><strong>Description:</strong><p>{{ $copyright->description }}</p></div>
                @if($copyright->meeting_requested_at)
                <div class="mb-3"><strong>Meeting Requested:</strong> {{ $copyright->meeting_requested_at->format('M d, Y H:i') }}</div>
                @endif
                @if($copyright->meeting_confirmed_at)
                <div class="mb-3"><strong>Meeting Confirmed:</strong> {{ $copyright->meeting_confirmed_at->format('M d, Y H:i') }}</div>
                @endif
                @if($copyright->registration_number)
                <div class="mb-3"><strong>Registration Number:</strong> <span class="text-success">{{ $copyright->registration_number }}</span></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>Client Information</h3></div>
            <div class="card-content">
                <p><strong>Name:</strong> {{ $copyright->user->name }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $copyright->user->email }}">{{ $copyright->user->email }}</a></p>
                @if($copyright->user->phone)<p><strong>Phone:</strong> <a href="tel:{{ $copyright->user->phone }}">{{ $copyright->user->phone }}</a></p>@endif
                <p><strong>Submitted:</strong> {{ $copyright->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>Actions</h3></div>
            <div class="card-content">
                @if(!$copyright->assignedTo)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> Assign Employee</button>
                @else
                <p><strong>Assigned to:</strong> {{ $copyright->assignedTo->name }}</p>
                @endif
                @if($copyright->isMeetingBooked() && !$copyright->meeting_confirmed_at)
                <button class="btn btn-success btn-block mb-2" onclick="showConfirmModal()"><i class="fas fa-check"></i> Confirm Meeting</button>
                @endif
                @if($copyright->isMeetingConfirmed())
                <button class="btn btn-primary btn-block mb-2" onclick="showStatusModal()"><i class="fas fa-edit"></i> Update Status</button>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Assign Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('copyright.assign', $copyright) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">Select employee...</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Assign</button></div></form></div></div></div>
<div class="modal fade" id="confirmModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Confirm Meeting</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('copyright.confirm-meeting', $copyright) }}">@csrf<div class="modal-body"><input type="url" name="meeting_link" class="form-control" placeholder="Meeting link (optional)"></div><div class="modal-footer"><button type="submit" class="btn btn-success">Confirm</button></div></form></div></div></div>
<div class="modal fade" id="statusModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Update Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('copyright.update-status', $copyright) }}">@csrf<div class="modal-body"><select name="status" class="form-control mb-2"><option value="filing">Filing</option><option value="registered">Registered</option><option value="completed">Completed</option></select><input type="text" name="registration_number" class="form-control" placeholder="Registration number (if registered)"></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>
function showAssignModal(){new bootstrap.Modal(document.getElementById('assignModal')).show();}
function showConfirmModal(){new bootstrap.Modal(document.getElementById('confirmModal')).show();}
function showStatusModal(){new bootstrap.Modal(document.getElementById('statusModal')).show();}
</script>
@endpush
 
