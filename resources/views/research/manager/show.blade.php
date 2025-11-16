@extends('layouts.internal-dashboard')
@section('title', 'Research Request Details')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('research.manager.index') }}">Research & IP</a></li>
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
                <div class="mb-3"><strong>Description:</strong><p>{{ $research->description }}</p></div>
                @if($research->requirements)
                <div class="mb-3"><strong>Requirements:</strong><p>{{ $research->requirements }}</p></div>
                @endif
                @if($research->nda_signed_at)
                <div class="mb-3"><strong>NDA Signed:</strong> {{ $research->nda_signed_at->format('M d, Y H:i') }}</div>
                @endif
                @if($research->sla_signed_at)
                <div class="mb-3"><strong>SLA Signed:</strong> {{ $research->sla_signed_at->format('M d, Y H:i') }}</div>
                @endif
                @if($research->meeting_scheduled_at)
                <div class="mb-3"><strong>Meeting Scheduled:</strong> {{ $research->meeting_scheduled_at->format('M d, Y H:i') }}</div>
                @endif
                @if($research->meeting_link)
                <div class="mb-3"><strong>Meeting Link:</strong> <a href="{{ $research->meeting_link }}" target="_blank">{{ $research->meeting_link }}</a></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3>Client Information</h3></div>
            <div class="card-content">
                <p><strong>Name:</strong> {{ $research->user->name }}</p>
                <p><strong>Email:</strong> <a href="mailto:{{ $research->user->email }}">{{ $research->user->email }}</a></p>
                @if($research->user->phone)<p><strong>Phone:</strong> <a href="tel:{{ $research->user->phone }}">{{ $research->user->phone }}</a></p>@endif
                <p><strong>Submitted:</strong> {{ $research->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h3>Actions</h3></div>
            <div class="card-content">
                @if($research->isNdaSigned() && !$research->assigned_to)
                <button class="btn btn-info btn-block mb-2" onclick="showAssignModal()"><i class="fas fa-user-plus"></i> Assign Employee</button>
                @endif
                @if($research->isInProgress())
                <button class="btn btn-success btn-block mb-2" onclick="markComplete()"><i class="fas fa-check"></i> Mark Complete</button>
                @endif
                @if($research->assignedTo)
                <p><strong>Assigned to:</strong> {{ $research->assignedTo->name }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Assign Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('research.assign', $research) }}">@csrf<div class="modal-body"><select name="assigned_to" class="form-control" required><option value="">Select employee...</option>@foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach</select></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Assign</button></div></form></div></div></div>
@endsection
@push('scripts')
<script>
function showAssignModal(){new bootstrap.Modal(document.getElementById('assignModal')).show();}
function markComplete(){if(confirm('Mark research as complete?')){fetch('{{ route('research.complete', $research) }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}}
</script>
@endpush
