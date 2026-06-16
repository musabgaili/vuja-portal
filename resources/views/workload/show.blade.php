@extends('layouts.internal-dashboard')
@section('title', $employee->name)

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-user-gear"></i> {{ $employee->name }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.workload.detail_subtitle') }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge" style="background:{{ $capColors[$capacity] }}; color:#fff; font-size:.8rem;">
            {{ __('portal.workload.cap.'.$capacity) }} · {{ __('portal.workload.load') }} {{ $load }}
        </span>
        <a href="{{ route('workload.index') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.workload.back') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

<div class="row g-3">
    {{-- Direct staff tasks --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><span class="card-title"><i class="fas fa-list-check"></i> {{ __('portal.workload.staff_tasks') }} ({{ $staffTasks->count() }})</span></div>
            <div class="card-content">
                {{-- Quick assign --}}
                <form method="POST" action="{{ route('staff-tasks.store') }}" class="mb-3">
                    @csrf
                    <input type="hidden" name="assigned_to" value="{{ $employee->id }}">
                    <input type="text" name="title" class="form-control form-control-sm mb-2" required maxlength="200" placeholder="{{ __('portal.workload.new_task_ph') }}">
                    <div class="d-flex gap-2">
                        <select name="category" class="form-select form-select-sm">
                            @foreach($categories as $c)<option value="{{ $c }}">{{ __('portal.staff_tasks.category.'.$c) }}</option>@endforeach
                        </select>
                        <select name="priority" class="form-select form-select-sm">
                            @foreach($priorities as $p)<option value="{{ $p }}" @selected($p==='normal')>{{ __('portal.staff_tasks.priority.'.$p) }}</option>@endforeach
                        </select>
                        <button class="btn btn-primary btn-sm" style="white-space:nowrap;"><i class="fas fa-plus"></i> {{ __('portal.workload.assign') }}</button>
                    </div>
                </form>

                @forelse($staffTasks as $t)
                    <div class="d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--gray-200); padding:.5rem 0;">
                        <div>
                            <strong>{{ $t->title }}</strong>
                            <div style="font-size:.78rem;">
                                <span class="badge bg-primary">{{ __('portal.staff_tasks.category.'.$t->category) }}</span>
                                <span class="badge bg-secondary">{{ __('portal.staff_tasks.priority.'.$t->priority) }}</span>
                                @if($t->due_date)<span class="text-muted">{{ $t->due_date->translatedFormat('M d') }}</span>@endif
                            </div>
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            <form method="POST" action="{{ route('staff-tasks.status', $t) }}">
                                @csrf
                                <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                    @foreach(['open','in_progress','done','cancelled'] as $s)
                                        <option value="{{ $s }}" @selected($t->status===$s)>{{ __('portal.staff_tasks.status.'.$s) }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route('staff-tasks.destroy', $t) }}" onsubmit="return confirm('{{ __('portal.staff_tasks.delete_confirm') }}');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-2">{{ __('portal.workload.no_staff_tasks') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Project tasks --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><span class="card-title"><i class="fas fa-diagram-project"></i> {{ __('portal.workload.project_tasks') }} ({{ $projectTasks->count() }})</span></div>
            <div class="card-content">
                {{-- Quick add to one of their projects --}}
                @if($memberProjects->isNotEmpty())
                <form id="ptAddForm" method="POST" class="mb-3" onsubmit="if(!document.getElementById('pt_project').value){return false;} this.action='{{ url('internal/projects') }}/'+document.getElementById('pt_project').value+'/tasks';">
                    @csrf
                    <input type="hidden" name="assigned_to" value="{{ $employee->id }}">
                    <div class="d-flex gap-2 mb-2">
                        <select id="pt_project" class="form-select form-select-sm" required>
                            <option value="">{{ __('portal.workload.choose_project') }}</option>
                            @foreach($memberProjects as $mp)<option value="{{ $mp->id }}">{{ $mp->title }}</option>@endforeach
                        </select>
                    </div>
                    <input type="text" name="title" class="form-control form-control-sm mb-2" required maxlength="255" placeholder="{{ __('portal.workload.new_ptask_ph') }}">
                    <div class="d-flex gap-2">
                        <select name="priority" class="form-select form-select-sm">
                            @foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected($p==='medium')>{{ __('portal.workload.prio.'.$p) }}</option>@endforeach
                        </select>
                        <input type="date" name="due_date" class="form-control form-control-sm">
                        <button class="btn btn-primary btn-sm" style="white-space:nowrap;"><i class="fas fa-plus"></i> {{ __('portal.workload.add') }}</button>
                    </div>
                </form>
                @endif

                @forelse($projectTasks as $t)
                    <div class="d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--gray-200); padding:.5rem 0;">
                        <div>
                            <strong>{{ $t->title }}</strong>
                            <div style="font-size:.78rem;">
                                @if($t->project)<a href="{{ route('projects.manager.show', $t->project) }}" class="badge bg-info text-decoration-none">{{ $t->project->title }}</a>@endif
                                <span class="badge bg-secondary">{{ $t->status }}</span>
                                @if($t->due_date)<span class="text-muted">{{ $t->due_date->translatedFormat('M d') }}</span>@endif
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            @if($t->project)<a href="{{ route('projects.manager.show', $t->project) }}" class="btn btn-sm btn-outline-primary" title="{{ __('portal.workload.edit_on_project') }}"><i class="fas fa-pen"></i></a>@endif
                            <form method="POST" action="{{ route('projects.tasks.destroy', $t) }}" onsubmit="return confirm('{{ __('portal.workload.delete_task_confirm') }}');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-2">{{ __('portal.workload.no_project_tasks') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Project memberships --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-folder-open"></i> {{ __('portal.workload.projects') }} ({{ $memberships->count() }})</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('portal.workload.project') }}</th><th>{{ __('portal.workload.role') }}</th><th>{{ __('portal.workload.status') }}</th><th class="text-end">{{ __('portal.team.col_actions') }}</th></tr></thead>
                    <tbody>
                        @forelse($memberships as $pp)
                            <tr>
                                <td>{{ $pp->project->title }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ', $pp->role)) }}</span></td>
                                <td>{{ $pp->project->getStatusLabel() }}</td>
                                <td class="text-end"><a href="{{ route('projects.manager.show', $pp->project) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> {{ __('portal.internal.view') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-3">{{ __('portal.workload.no_projects') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
