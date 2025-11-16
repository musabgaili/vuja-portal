@extends('layouts.internal-dashboard')
@section('title', 'All Projects')
@section('breadcrumbs')
<li class="breadcrumb-item active">Projects</li>
@endsection

@section('content')
<style>
.projects-header{background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);color:white;padding:2rem;border-radius:12px;margin-bottom:1.5rem;box-shadow:0 4px 16px rgba(99,102,241,0.3);}
.filter-card{background:white;border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.5rem;}
.table-modern{background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);}
.table-modern thead{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);}
.table-modern th{padding:1rem;font-weight:600;color:#1e293b;border-bottom:2px solid #e2e8f0;font-size:0.875rem;text-transform:uppercase;}
.table-modern td{padding:1rem;vertical-align:middle;border-bottom:1px solid #f1f5f9;}
.table-modern tbody tr:hover{background:#f8fafc;}
.stat-cards-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;}
.stat-card-proj{background:white;border-radius:12px;padding:1.5rem;box-shadow:0 4px 16px rgba(0,0,0,0.08);border-left:4px solid;transition:all 0.3s;}
.stat-card-proj:hover{transform:translateY(-4px);box-shadow:0 8px 24px rgba(0,0,0,0.12);}
.stat-number-proj{font-size:2.5rem;font-weight:700;}
.stat-label-proj{color:#64748b;font-size:0.875rem;text-transform:uppercase;margin-top:0.5rem;}
</style>

<div class="projects-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 style="margin:0;font-size:1.75rem;font-weight:700;"><i class="fas fa-folder-open"></i> All Projects</h1>
            <p style="margin:0.5rem 0 0 0;opacity:0.95;">Manage and track all projects</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('projects.manager.index') }}" class="btn btn-light">Table View</a>
            <a href="{{ route('projects.kanban') }}" class="btn btn-outline-light">Kanban</a>
            @if(auth()->user()->isManager())
            <a href="{{ route('projects.create') }}" class="btn btn-warning"><i class="fas fa-plus"></i> New</a>
            @endif
        </div>
    </div>
</div>

<div class="filter-card">
    <form method="GET">
        <div class="row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="form-label fw-bold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, client email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="planning" {{ request('status')=='planning'?'selected':'' }}>Planning</option>
                    <option value="quoted" {{ request('status')=='quoted'?'selected':'' }}>Quoted</option>
                    <option value="awarded" {{ request('status')=='awarded'?'selected':'' }}>Awarded</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="paused" {{ request('status')=='paused'?'selected':'' }}>Paused</option>
                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                    <option value="lost" {{ request('status')=='lost'?'selected':'' }}>Lost</option>
                    <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search</button>
            </div>
            @if(request()->hasAny(['search','status']))
            <div class="col-md-2">
                <a href="{{ route('projects.manager.index') }}" class="btn btn-secondary w-100"><i class="fas fa-redo"></i> Clear</a>
            </div>
            @endif
        </div>
    </form>
</div>

<div class="stat-cards-row">
    <div class="stat-card-proj" style="border-color:#3b82f6;">
        <div class="stat-number-proj" style="color:#3b82f6;">{{ $stats['total']??0 }}</div>
        <div class="stat-label-proj">Total Projects</div>
    </div>
    <div class="stat-card-proj" style="border-color:#10b981;">
        <div class="stat-number-proj" style="color:#10b981;">{{ $stats['in_progress']??0 }}</div>
        <div class="stat-label-proj">In Progress</div>
    </div>
    <div class="stat-card-proj" style="border-color:#f59e0b;">
        <div class="stat-number-proj" style="color:#f59e0b;">{{ $stats['planning']??0 }}</div>
        <div class="stat-label-proj">Planning</div>
    </div>
    <div class="stat-card-proj" style="border-color:#8b5cf6;">
        <div class="stat-number-proj" style="color:#8b5cf6;">{{ $stats['completed']??0 }}</div>
        <div class="stat-label-proj">Completed</div>
    </div>
</div>

@if($projects->count() > 0)
<div style="overflow-x:auto;">
<div class="table-modern">
    <table class="table mb-0" style="min-width:900px;">
        <thead>
            <tr>
                <th>Project</th>
                <th>Client</th>
                <th>Status</th>
                <th>Progress</th>
                <th>PM</th>
                <th>Budget</th>
                <th>Dates</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $p)
            <tr>
                <td>
                    <strong style="color:#1e293b;">{{ $p->title }}</strong>
                    <br><small class="text-muted">#{{ $p->id }}</small>
                </td>
                <td>
                    @if($p->client)
                        <strong>{{ $p->client->name }}</strong>
                        <br><small class="text-muted">{{ $p->client->email }}</small>
                    @else
                        <span class="text-muted">No client</span>
                    @endif
                </td>
                <td><span class="status-badge {{ $p->getStatusBadgeColor() }}">{{ $p->getStatusLabel() }}</span></td>
                <td>
                    <div class="progress" style="height:8px;border-radius:10px;">
                        <div class="progress-bar bg-success" style="width:{{ $p->completion_percentage }}%"></div>
                    </div>
                    <small class="text-muted">{{ $p->completion_percentage }}%</small>
                </td>
                <td>
                    @if($p->projectManager)
                        <span class="badge bg-info">{{ $p->projectManager->name }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($p->budget)
                        <strong style="color:#10b981;">${{ number_format($p->budget,2) }}</strong>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($p->start_date)
                        <small class="text-muted">{{ $p->start_date->format('M d, Y') }}</small>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('projects.manager.show',$p) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $projects->links('pagination::bootstrap-5') }}
</div>
@else
<div class="text-center py-5" style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
    <i class="fas fa-folder-open" style="font-size:4rem;color:#cbd5e1;margin-bottom:1rem;"></i>
    <h4 style="color:#1e293b;font-weight:600;">No Projects</h4>
    <p class="text-muted">No projects match your criteria.</p>
</div>
@endif
@endsection
