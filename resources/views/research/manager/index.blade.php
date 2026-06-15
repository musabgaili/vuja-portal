@extends('layouts.internal-dashboard')
@section('title', __('portal.research.manager.index.page_title'))
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('research.manager.index') }}">{{ __('portal.research.manager.index.breadcrumb_parent') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.research.manager.index.all_requests_breadcrumb') }}</li>
@endsection

@section('content')
<style>
.research-header{background:linear-gradient(135deg,#10b981 0%,#2C3F43 100%);color:white;padding:2rem;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(16,185,129,0.3);}
.table-modern{background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.table-modern thead{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
.table-modern th{padding:1rem;font-weight:600;color:#1e293b;border-bottom:2px solid #e2e8f0;font-size:0.875rem;text-transform:uppercase;}
.table-modern td{padding:1rem;vertical-align:middle;border-bottom:1px solid #f1f5f9;}
.table-modern tbody tr:hover{background:#f8fafc;}
.empty-state{text-align:center;padding:4rem;background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.empty-state i{font-size:4rem;color:#cbd5e1;margin-bottom:1rem;}
</style>

<div class="research-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-search"></i> {{ __('portal.research.manager.index.heading') }}</h1><p style="margin:0.5rem 0 0 0;opacity:0.95;">{{ __('portal.research.manager.index.subtitle') }}</p></div>
        <div class="text-end"><h2 style="margin:0;font-size:2.5rem;font-weight:700;">{{ $researches->total() }}</h2><small style="opacity:0.9;">{{ __('portal.research.manager.index.total_label') }}</small></div>
    </div>
</div>

@if($researches->count() > 0)
<div class="table-modern">
    <table class="table mb-0">
        <thead><tr><th>{{ __('portal.research.manager.index.col_id') }}</th><th>{{ __('portal.research.manager.index.col_title') }}</th><th>{{ __('portal.research.manager.index.col_client') }}</th><th>{{ __('portal.research.manager.index.col_status') }}</th><th>{{ __('portal.research.manager.index.col_assigned') }}</th><th>{{ __('portal.research.manager.index.col_actions') }}</th></tr></thead>
        <tbody>
            @foreach($researches as $r)
            <tr>
                <td><strong style="color:#10b981;">#{{ $r->id }}</strong></td>
                <td><strong style="color:#1e293b;">{{ $r->title }}</strong></td>
                <td><strong>{{ $r->user->name }}</strong><br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $r->user->email }}</small></td>
                <td><span class="status-badge {{ $r->getStatusBadgeColor() }}">{{ $r->getStatusLabel() }}</span></td>
                <td>@if($r->assignedTo)<span class="badge bg-success">{{ $r->assignedTo->name }}</span>@else<span class="text-muted">â€”</span>@endif</td>
                <td><div class="d-flex gap-2"><a href="{{ route('research.manager.show',$r) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>@if($r->isNdaSigned()&&!$r->assignedTo)<button class="btn btn-sm btn-primary" onclick="assign(@js($r->getRouteKey()))"><i class="fas fa-user-plus"></i></button>@endif @if($r->isInProgress())<button class="btn btn-sm btn-success" onclick="complete(@js($r->getRouteKey()))"><i class="fas fa-check"></i></button>@endif</div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $researches->links('pagination::bootstrap-5') }}</div>
@else
<div class="empty-state"><i class="fas fa-search"></i><h4 style="color:#1e293b;font-weight:600;">{{ __('portal.research.manager.index.empty_title') }}</h4><p class="text-muted">{{ __('portal.research.manager.index.empty_body') }}</p></div>
@endif
@endsection

@push('scripts')
<script>
// Assigning needs an employee and completing needs the research findings, so
// these actions open the detail page where the proper forms live (a bare POST
// here failed validation silently).
function assign(id){window.location.href=`/internal/research/manager/${id}`;}
function complete(id){window.location.href=`/internal/research/manager/${id}`;}
</script>
@endpush
