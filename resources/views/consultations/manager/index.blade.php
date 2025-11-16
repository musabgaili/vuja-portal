@extends('layouts.internal-dashboard')
@section('title', 'Consultations')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('consultations.manager.index') }}">Consultations</a></li>
<li class="breadcrumb-item active">All Requests</li>
@endsection

@section('content')
<style>
.consul-header{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:white;padding:2rem;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(59,130,246,0.3);}
.table-modern{background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.table-modern thead{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
.table-modern th{padding:1rem;font-weight:600;color:#1e293b;border-bottom:2px solid #e2e8f0;font-size:0.875rem;text-transform:uppercase;letter-spacing:0.5px;}
.table-modern td{padding:1rem;vertical-align:middle;border-bottom:1px solid #f1f5f9;}
.table-modern tbody tr{transition:all 0.2s;}
.table-modern tbody tr:hover{background:#f8fafc;}
.empty-state{text-align:center;padding:4rem 2rem;background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.empty-state i{font-size:4rem;color:#cbd5e1;margin-bottom:1rem;}
.filter-card{background:white;border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.5rem;}
</style>

<div class="consul-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-comments"></i> Consultations</h1><p style="margin:0.5rem 0 0 0;opacity:0.95;">Manage all consultation requests</p></div>
        <div class="text-end"><h2 style="margin:0;font-size:2.5rem;font-weight:700;">{{ $consultations->total() }}</h2><small style="opacity:0.9;">Total</small></div>
    </div>
</div>

<div class="filter-card">
    <div class="row align-items-end">
        <div class="col-md-3"><label class="form-label fw-bold">Filter by Status</label><select class="form-control" onchange="filterByStatus(this.value)"><option value="">All</option><option value="submitted">Submitted</option><option value="assigned">Assigned</option><option value="meeting_scheduled">Meeting Scheduled</option><option value="completed">Completed</option></select></div>
    </div>
</div>

@if($consultations->count() > 0)
<div class="table-modern">
    <table class="table mb-0">
        <thead><tr><th>ID</th><th>Title</th><th>Client</th><th>Category</th><th>Status</th><th>Assigned</th><th>Meeting</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($consultations as $c)
            <tr>
                <td><strong style="color:#3b82f6;">#{{ $c->id }}</strong></td>
                <td><strong style="color:#1e293b;">{{ $c->title }}</strong><br><small class="text-muted">{{ Str::limit($c->description,50) }}</small></td>
                <td><strong>{{ $c->user->name }}</strong><br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $c->user->email }}</small></td>
                <td><span class="badge bg-info">{{ $c->category }}</span></td>
                <td><span class="status-badge {{ $c->getStatusBadgeColor() }}">{{ $c->getStatusLabel() }}</span></td>
                <td>@if($c->assignedTo)<span class="badge bg-success">{{ $c->assignedTo->name }}</span>@else<span class="text-muted">—</span>@endif</td>
                <td>@if($c->meeting)<small class="text-info"><i class="fas fa-calendar"></i> {{ $c->meeting->scheduled_at->format('M d, g:i A') }}</small>@else<span class="text-muted">—</span>@endif</td>
                <td><div class="d-flex gap-2"><a href="{{ route('consultations.manager.show',$c) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>@if($c->isSubmitted())<button class="btn btn-sm btn-primary" onclick="showAssignModal({{ $c->id }})"><i class="fas fa-user-plus"></i></button>@endif</div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $consultations->links('pagination::bootstrap-5') }}</div>
@else
<div class="empty-state"><i class="fas fa-comments"></i><h4 style="color:#1e293b;font-weight:600;">No Consultations</h4><p class="text-muted">No consultation requests yet.</p></div>
@endif

<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5><i class="fas fa-user-plus"></i> Assign Employee</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="POST" id="assignForm">@csrf<div class="modal-body"><div class="form-group"><label class="form-label fw-bold">Select Employee *</label><select name="assigned_to" class="form-control" required><option value="">Choose...</option>@foreach(\App\Models\User::where('type','internal')->where('status','active')->get() as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Assign</button></div></form></div></div></div>
@endsection

@push('scripts')
<script>
function showAssignModal(id){document.getElementById('assignForm').action=`/internal/consultations/${id}/assign`;new bootstrap.Modal(document.getElementById('assignModal')).show();}
function filterByStatus(s){const u=new URL(window.location);s?u.searchParams.set('status',s):u.searchParams.delete('status');window.location.href=u.toString();}
</script>
@endpush
