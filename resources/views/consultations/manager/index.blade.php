@extends('layouts.internal-dashboard')
@section('title', __('portal.consultations.manager.index.title'))
@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('consultations.manager.index') }}">{{ __('portal.consultations.manager.index.breadcrumb') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.consultations.manager.index.all_requests') }}</li>
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
.consul-title-link{color:var(--primary-color);text-decoration:none;}
.consul-title-link:hover{text-decoration:underline;}
</style>

<div class="consul-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-comments"></i> {{ __('portal.consultations.manager.index.heading') }}</h1><p style="margin:0.5rem 0 0 0;opacity:0.95;">{{ __('portal.consultations.manager.index.subtitle') }}</p></div>
        <div class="text-end"><h2 style="margin:0;font-size:2.5rem;font-weight:700;">{{ $consultations->total() }}</h2><small style="opacity:0.9;">{{ __('portal.consultations.manager.index.total') }}</small></div>
    </div>
</div>

<div class="filter-card">
    <div class="row align-items-end">
        <div class="col-md-3"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.filter_by_status') }}</label><select class="form-control" onchange="filterByStatus(this.value)"><option value="">{{ __('portal.consultations.manager.index.all') }}</option><option value="submitted">{{ __('portal.consultations.manager.status.submitted') }}</option><option value="assigned">{{ __('portal.consultations.manager.status.assigned') }}</option><option value="meeting_scheduled">{{ __('portal.consultations.manager.status.meeting_scheduled') }}</option><option value="completed">{{ __('portal.consultations.manager.status.completed') }}</option></select></div>
    </div>
</div>

@if($consultations->count() > 0)
<div class="table-modern">
    <table class="table mb-0">
        <thead><tr><th>{{ __('portal.consultations.manager.index.col_id') }}</th><th>{{ __('portal.consultations.manager.index.col_title') }}</th><th>{{ __('portal.consultations.manager.index.col_client') }}</th><th>{{ __('portal.consultations.manager.index.col_category') }}</th><th>{{ __('portal.consultations.manager.index.col_status') }}</th><th>{{ __('portal.consultations.manager.index.col_assigned') }}</th><th>{{ __('portal.consultations.manager.index.col_meeting') }}</th><th>{{ __('portal.consultations.manager.index.col_actions') }}</th></tr></thead>
        <tbody>
            @foreach($consultations as $c)
            <tr>
                <td><strong style="color:#3b82f6;">#{{ $c->id }}</strong></td>
                <td><a href="{{ route('consultations.manager.show',$c) }}" class="consul-title-link"><strong>{{ $c->title }}</strong></a><br><small class="text-muted">{{ Str::limit($c->description,50) }}</small></td>
                <td><strong>{{ $c->user->name }}</strong><br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $c->user->email }}</small></td>
                <td><span class="badge bg-info">{{ $c->category }}</span></td>
                <td><span class="status-badge {{ $c->getStatusBadgeColor() }}">{{ $c->getStatusLabel() }}</span></td>
                <td>@if($c->assignedTo)<span class="badge bg-success">{{ $c->assignedTo->name }}</span>@else<span class="text-muted">{{ __('portal.consultations.manager.index.none_dash') }}</span>@endif</td>
                <td>@if($c->meeting_scheduled_at)<small class="text-info"><i class="fas fa-calendar"></i> {{ $c->meeting_scheduled_at->format('M d, g:i A') }}</small>@else<span class="text-muted">{{ __('portal.consultations.manager.index.none_dash') }}</span>@endif</td>
                <td><div class="d-flex gap-2 flex-wrap"><a href="{{ route('consultations.manager.show',$c) }}" class="btn btn-sm btn-secondary" title="{{ __('portal.consultations.manager.index.view') }}"><i class="fas fa-eye"></i></a>@if($c->isSubmitted() || $c->isFiltered() || $c->isAssigned())<button class="btn btn-sm btn-primary" onclick="showAssignModal(@js($c->getRouteKey()))"><i class="fas fa-user-plus"></i> {{ __('portal.consultations.manager.index.assign_employee') }}</button>@endif</div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $consultations->links('pagination::bootstrap-5') }}</div>
@else
<div class="empty-state"><i class="fas fa-comments"></i><h4 style="color:#1e293b;font-weight:600;">{{ __('portal.consultations.manager.index.empty_title') }}</h4><p class="text-muted">{{ __('portal.consultations.manager.index.empty_body') }}</p></div>
@endif

<div class="modal fade" id="assignModal"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5><i class="fas fa-user-plus"></i> {{ __('portal.consultations.manager.index.assign_schedule_title') }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <form method="POST" id="assignForm">@csrf
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-bold">{{ __('portal.consultations.manager.index.select_employee') }} *</label>
                <select name="assigned_to" id="ae_employee" class="form-control" required onchange="loadEmployeeSlots(this.value)">
                    <option value="">{{ __('portal.consultations.manager.index.choose') }}</option>
                    @foreach(\App\Models\User::where('type','internal')->where('status','active')->orderBy('name')->get() as $e)
                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="mode" id="ae_mode" value="existing">
            <div class="btn-group w-100 mb-3" role="group">
                <button type="button" class="btn btn-outline-primary active" id="ae_tab_existing" onclick="setMode('existing')"><i class="fas fa-calendar-check"></i> {{ __('portal.consultations.manager.index.existing_slot') }}</button>
                <button type="button" class="btn btn-outline-primary" id="ae_tab_new" onclick="setMode('new')"><i class="fas fa-calendar-plus"></i> {{ __('portal.consultations.manager.index.new_slot') }}</button>
            </div>
            <div id="ae_existing_block">
                <label class="form-label fw-bold">{{ __('portal.consultations.manager.index.available_slots') }}</label>
                <select name="time_slot_id" id="ae_slot" class="form-control"><option value="">{{ __('portal.consultations.manager.index.select_employee_first') }}</option></select>
                <small class="text-muted d-block mt-1" id="ae_slot_hint"></small>
            </div>
            <div id="ae_new_block" style="display:none;">
                <div class="row g-2">
                    <div class="col-md-4"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.date') }}</label><input type="date" name="date" id="ae_date" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.start') }}</label><input type="time" name="start_time" id="ae_start" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.end') }}</label><input type="time" name="end_time" id="ae_end" class="form-control"></div>
                </div>
                <small class="text-muted d-block mt-1">{{ __('portal.consultations.manager.index.new_slot_hint') }}</small>
            </div>
            <div class="mt-3"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.meeting_link') }}</label><input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..."></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.consultations.manager.index.cancel') }}</button><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.consultations.manager.index.assign_schedule') }}</button></div>
    </form>
</div></div></div>
@endsection

@push('scripts')
<script>
function showAssignModal(id){
    document.getElementById('assignForm').action=`/internal/consultations/${id}/assign-and-schedule`;
    document.getElementById('ae_employee').value='';
    document.getElementById('ae_slot').innerHTML='<option value="">'+@json(__('portal.consultations.manager.index.select_employee_first'))+'</option>';
    document.getElementById('ae_slot_hint').textContent='';
    setMode('existing');
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}
function setMode(m){
    document.getElementById('ae_mode').value=m;
    document.getElementById('ae_existing_block').style.display=m==='existing'?'block':'none';
    document.getElementById('ae_new_block').style.display=m==='new'?'block':'none';
    document.getElementById('ae_tab_existing').classList.toggle('active',m==='existing');
    document.getElementById('ae_tab_new').classList.toggle('active',m==='new');
}
function loadEmployeeSlots(id){
    const sel=document.getElementById('ae_slot');
    const hint=document.getElementById('ae_slot_hint');
    if(!id){sel.innerHTML='<option value="">'+@json(__('portal.consultations.manager.index.select_employee_first'))+'</option>';return;}
    hint.textContent=@json(__('portal.consultations.manager.index.loading'));
    fetch(`/internal/time-slots/available/${id}`,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
        .then(r=>r.json())
        .then(d=>{
            const slots=(d&&d.slots)||[];
            if(slots.length===0){
                sel.innerHTML='<option value="">'+@json(__('portal.consultations.manager.index.no_slots'))+'</option>';
                hint.textContent=@json(__('portal.consultations.manager.index.no_slots_hint'));
                setMode('new');
            }else{
                sel.innerHTML=slots.map(s=>`<option value="${s.id}">${s.date} · ${s.start_time}–${s.end_time} (${s.duration}m)</option>`).join('');
                hint.textContent='';
                setMode('existing');
            }
        })
        .catch(()=>{hint.textContent=@json(__('portal.consultations.manager.index.slots_error'));setMode('new');});
}
function filterByStatus(s){const u=new URL(window.location);s?u.searchParams.set('status',s):u.searchParams.delete('status');window.location.href=u.toString();}
</script>
@endpush
