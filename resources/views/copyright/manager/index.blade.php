@extends('layouts.internal-dashboard')
@section('title', 'Copyright Registrations')
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('copyright.manager.index') }}">Copyright</a></li>
<li class="breadcrumb-item active">All Requests</li>
@endsection

@section('content')
<style>
.copy-header{background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);color:white;padding:2rem;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(239,68,68,0.3);}
.table-modern{background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.table-modern thead{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
.table-modern th{padding:1rem;font-weight:600;color:#1e293b;border-bottom:2px solid #e2e8f0;font-size:0.875rem;text-transform:uppercase;}
.table-modern td{padding:1rem;vertical-align:middle;border-bottom:1px solid #f1f5f9;}
.table-modern tbody tr:hover{background:#f8fafc;}
.empty-state{text-align:center;padding:4rem;background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.empty-state i{font-size:4rem;color:#cbd5e1;margin-bottom:1rem;}
</style>

<div class="copy-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-copyright"></i> Copyright Registrations</h1><p style="margin:0.5rem 0 0 0;opacity:0.95;">Manage copyright registration requests</p></div>
        <div class="text-end"><h2 style="margin:0;font-size:2.5rem;font-weight:700;">{{ $copyrights->total() }}</h2><small style="opacity:0.9;">Total</small></div>
    </div>
</div>

@if($copyrights->count() > 0)
<div class="table-modern">
    <table class="table mb-0">
        <thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Client</th><th>Status</th><th>Copyright#</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($copyrights as $c)
            <tr>
                <td><strong style="color:#ef4444;">#{{ $c->id }}</strong></td>
                <td><strong style="color:#1e293b;">{{ $c->title }}</strong></td>
                <td><span class="badge bg-danger">{{ $c->work_type }}</span></td>
                <td><strong>{{ $c->user->name }}</strong><br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $c->user->email }}</small></td>
                <td><span class="status-badge {{ $c->getStatusBadgeColor() }}">{{ $c->getStatusLabel() }}</span></td>
                <td>{{ $c->copyright_number??'—' }}</td>
                <td><div class="d-flex gap-2"><a href="{{ route('copyright.manager.show',$c) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>@if($c->isMeetingBooked()&&!$c->meeting_confirmed_at)<button class="btn btn-sm btn-success" onclick="confirmMeeting({{ $c->id }})"><i class="fas fa-check"></i></button>@endif @if($c->isMeetingConfirmed())<button class="btn btn-sm btn-primary" onclick="updateStatus({{ $c->id }})"><i class="fas fa-edit"></i></button>@endif</div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $copyrights->links('pagination::bootstrap-5') }}</div>
@else
<div class="empty-state"><i class="fas fa-copyright"></i><h4 style="color:#1e293b;font-weight:600;">No Copyright Registrations</h4><p class="text-muted">No copyright registration requests yet.</p></div>
@endif
@endsection

@push('scripts')
<script>
function confirmMeeting(id){if(confirm('Confirm?'))fetch(`/internal/copyright/${id}/confirm-meeting`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}
function updateStatus(id){window.location.href=`/internal/copyright/${id}`;}
</script>
@endpush
