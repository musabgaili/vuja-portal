@extends('layouts.internal-dashboard')
@section('title', 'IP Registration Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('ip.manager.index') }}">IP Registration</a></li>
<li class="breadcrumb-item active">#{{ $ip->id }} - {{ Str::limit($ip->title, 30) }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $ip->title }}</h3>
                <span class="status-badge {{ $ip->getStatusBadgeColor() }}">{{ $ip->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="mb-3"><strong>IP Type:</strong> <span class="badge bg-primary">{{ $ip->ip_type }}</span></div>
                <div class="mb-3"><strong>Description:</strong><p>{{ $ip->description }}</p></div>
                @if($ip->meeting_requested_at)
                <div class="mb-3"><strong>Meeting Requested:</strong> {{ $ip->meeting_requested_at->format('M d, Y H:i') }}</div>
                @endif
                @if($ip->meeting_confirmed_at)
                <div class="mb-3"><strong>Meeting Confirmed:</strong> {{ $ip->meeting_confirmed_at->format('M d, Y H:i') }}</div>
                @endif
                @if($ip->registration_number)
                <div class="mb-3"><strong>Registration Number:</strong> <span class="text-success">{{ $ip->registration_number }}</span></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>Client Information</h3></div>
            <div class="card-content">
                <p><strong>Name:</strong> {{ $ip->user->name }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $ip->user->email }}">{{ $ip->user->email }}</a></p>
                @if($ip->user->phone)<p><strong>Phone:</strong> <a href="tel:{{ $ip->user->phone }}">{{ $ip->user->phone }}</a></p>@endif
                <p><strong>Submitted:</strong> {{ $ip->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>Actions</h3></div>
            <div class="card-content">
                @if(!$ip->assignedTo)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> Assign Employee</button>
                @else
                <p><strong>Assigned to:</strong> {{ $ip->assignedTo->name }}</p>
                @endif
                @if($ip->isMeetingBooked() && !$ip->meeting_confirmed_at)
                <button class="btn btn-success btn-block mb-2" onclick="showConfirmModal()"><i class="fas fa-check"></i> Confirm Meeting</button>
                @endif
                @if($ip->isMeetingConfirmed())
                <button class="btn btn-primary btn-block mb-2" onclick="showStatusModal()"><i class="fas fa-edit"></i> Update Status</button>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Assign Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('ip.assign', $ip) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">Select employee...</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Assign</button></div></form></div></div></div>
<div class="modal fade" id="confirmModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Confirm Meeting</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('ip.confirm-meeting', $ip) }}">@csrf<div class="modal-body"><input type="url" name="meeting_link" class="form-control" placeholder="Meeting link (optional)"></div><div class="modal-footer"><button type="submit" class="btn btn-success">Confirm</button></div></form></div></div></div>
<div class="modal fade" id="statusModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Update Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('ip.update-status', $ip) }}">@csrf<div class="modal-body"><select name="status" class="form-control mb-2"><option value="documentation">Documentation</option><option value="filing">Filing</option><option value="registered">Registered</option><option value="completed">Completed</option></select><input type="text" name="registration_number" class="form-control" placeholder="Registration number (if registered)"></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>
function showAssignModal(){new bootstrap.Modal(document.getElementById('assignModal')).show();}
function showConfirmModal(){new bootstrap.Modal(document.getElementById('confirmModal')).show();}
function showStatusModal(){new bootstrap.Modal(document.getElementById('statusModal')).show();}
</script>
@endpush
@endsection

