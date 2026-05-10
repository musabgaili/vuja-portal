@extends('layouts.internal-dashboard')
@section('title', __('portal.ip.registrations'))
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('ip.manager.index') }}">{{ __('portal.ip.registration') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.ip.all_requests') }}</li>
@endsection

@section('content')
<style>
.ip-header{background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);color:white;padding:2rem;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(139,92,246,0.3);}
.table-modern{background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.table-modern thead{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
.table-modern th{padding:1rem;font-weight:600;color:#1e293b;border-bottom:2px solid #e2e8f0;font-size:0.875rem;text-transform:uppercase;}
.table-modern td{padding:1rem;vertical-align:middle;border-bottom:1px solid #f1f5f9;}
.table-modern tbody tr:hover{background:#f8fafc;}
.empty-state{text-align:center;padding:4rem;background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.empty-state i{font-size:4rem;color:#cbd5e1;margin-bottom:1rem;}
</style>

<div class="ip-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-file-contract"></i> {{ __('portal.ip.registrations') }}</h1><p style="margin:0.5rem 0 0 0;opacity:0.95;">{{ __('portal.ip.manage_registration_requests') }}</p></div>
        <div class="text-end"><h2 style="margin:0;font-size:2.5rem;font-weight:700;">{{ $registrations->total() }}</h2><small style="opacity:0.9;">{{ __('portal.ip.total') }}</small></div>
    </div>
</div>

@if($registrations->count() > 0)
<div class="table-modern">
    <table class="table mb-0">
        <thead><tr><th>{{ __('portal.ip.id') }}</th><th>{{ __('portal.ip.title') }}</th><th>{{ __('portal.ip.type') }}</th><th>{{ __('portal.ip.client') }}</th><th>{{ __('portal.ip.status') }}</th><th>{{ __('portal.ip.reg_number_short') }}</th><th>{{ __('portal.team.col_actions') }}</th></tr></thead>
        <tbody>
            @foreach($registrations as $ip)
            <tr>
                <td><strong style="color:#8b5cf6;">#{{ $ip->id }}</strong></td>
                <td><strong style="color:#1e293b;">{{ $ip->title }}</strong></td>
                <td><span class="badge bg-primary">{{ $ip->ip_type }}</span></td>
                <td><strong>{{ $ip->user->name }}</strong><br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $ip->user->email }}</small></td>
                <td><span class="status-badge {{ $ip->getStatusBadgeColor() }}">{{ $ip->getStatusLabel() }}</span></td>
                <td>{{ $ip->registration_number??'—' }}</td>
                <td><div class="d-flex gap-2"><a href="{{ route('ip.manager.show',$ip) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>@if($ip->isMeetingBooked()&&!$ip->meeting_confirmed_at)<button class="btn btn-sm btn-success" onclick="confirmMeeting(@js($ip->getRouteKey()))"><i class="fas fa-check"></i></button>@endif @if($ip->isMeetingConfirmed())<button class="btn btn-sm btn-primary" onclick="updateStatus(@js($ip->getRouteKey()))"><i class="fas fa-edit"></i></button>@endif</div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $registrations->links('pagination::bootstrap-5') }}</div>
@else
<div class="empty-state"><i class="fas fa-file-contract"></i><h4 style="color:#1e293b;font-weight:600;">{{ __('portal.ip.no_registrations_title') }}</h4><p class="text-muted">{{ __('portal.ip.no_registrations_body') }}</p></div>
@endif
@endsection

@push('scripts')
<script>
function confirmMeeting(id){if(confirm('{{ __('portal.ip.confirm_meeting_question') }}'))fetch(`/internal/ip/${id}/confirm-meeting`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}
function updateStatus(id){window.location.href=`/internal/ip/${id}`;}
</script>
@endpush
