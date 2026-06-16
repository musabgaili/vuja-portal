@extends('layouts.internal-dashboard')
@section('title', __('portal.staff_tasks.title'))

@php
    $statusBadge = [
        'open' => 'secondary', 'in_progress' => 'info', 'done' => 'success', 'cancelled' => 'dark',
    ];
    $priorityBadge = [
        'low' => 'secondary', 'normal' => 'primary', 'high' => 'warning', 'urgent' => 'danger',
    ];
@endphp

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-list-check"></i> {{ __('portal.staff_tasks.title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ $isManager ? __('portal.staff_tasks.subtitle_manager') : __('portal.staff_tasks.subtitle_employee') }}</p>
    </div>
    @if($isManager)
    <div class="d-flex gap-2">
        <a href="{{ route('imports.form', 'staff-tasks') }}" class="btn btn-light"><i class="fas fa-file-excel"></i> {{ __('portal.import.import') }}</a>
        <a href="{{ route('staff-tasks.create') }}" class="btn btn-light">
            <i class="fas fa-plus"></i> {{ __('portal.staff_tasks.new') }}
        </a>
    </div>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($isManager)
<div class="card mb-3">
    <div class="card-content">
        <form method="GET" action="{{ route('staff-tasks.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.staff_tasks.field.assignee') }}</label>
                <select name="assignee" class="form-select form-select-sm">
                    <option value="">{{ __('portal.staff_tasks.filter.all') }}</option>
                    @foreach($staff as $person)
                    <option value="{{ $person->id }}" @selected($filters['assignee'] == $person->id)>{{ $person->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.staff_tasks.field.category') }}</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">{{ __('portal.staff_tasks.filter.all') }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected($filters['category'] === $cat)>{{ __('portal.staff_tasks.category.'.$cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.staff_tasks.field.status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('portal.staff_tasks.filter.all') }}</option>
                    @foreach($statuses as $st)
                    <option value="{{ $st }}" @selected($filters['status'] === $st)>{{ __('portal.staff_tasks.status.'.$st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> {{ __('portal.staff_tasks.filter.apply') }}</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-content">
        @if($tasks->isEmpty())
            <p class="text-muted text-center my-4">{{ __('portal.staff_tasks.empty') }}</p>
        @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>{{ __('portal.staff_tasks.field.title') }}</th>
                        <th>{{ __('portal.staff_tasks.field.category') }}</th>
                        @if($isManager)<th>{{ __('portal.staff_tasks.field.assignee') }}</th>@endif
                        <th>{{ __('portal.staff_tasks.field.priority') }}</th>
                        <th>{{ __('portal.staff_tasks.field.due_date') }}</th>
                        <th>{{ __('portal.staff_tasks.field.status') }}</th>
                        <th class="text-end">{{ __('portal.staff_tasks.field.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr>
                        <td>
                            <strong>{{ $task->title }}</strong>
                            @if($task->description)<div class="text-muted" style="font-size:.8rem;">{{ \Illuminate\Support\Str::limit($task->description, 80) }}</div>@endif
                            @if($task->project)<span class="badge bg-light text-dark"><i class="fas fa-folder"></i> {{ $task->project->title }}</span>@endif
                            @if($task->opportunity)<span class="badge bg-light text-dark"><i class="fas fa-funnel-dollar"></i> {{ $task->opportunity->name }}</span>@endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ __('portal.staff_tasks.category.'.$task->category) }}</span>
                            <div class="text-muted" style="font-size:.75rem;">+{{ $rates[$task->engagementAction()] ?? 0 }} {{ __('portal.staff_tasks.ip') }}</div>
                        </td>
                        @if($isManager)<td>{{ $task->assignee->name ?? '—' }}</td>@endif
                        <td><span class="badge bg-{{ $priorityBadge[$task->priority] ?? 'secondary' }}">{{ __('portal.staff_tasks.priority.'.$task->priority) }}</span></td>
                        <td>
                            @if($task->due_date)
                                <span class="{{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $task->due_date->translatedFormat('M d, Y') }}</span>
                                @if($task->isOverdue())<i class="fas fa-triangle-exclamation text-danger" title="{{ __('portal.staff_tasks.overdue') }}"></i>@endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $statusBadge[$task->status] ?? 'secondary' }}">{{ __('portal.staff_tasks.status.'.$task->status) }}</span></td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                @if($task->status !== 'done' && $task->status !== 'cancelled')
                                    @if($task->status === 'open')
                                    <form method="POST" action="{{ route('staff-tasks.status', $task) }}">
                                        @csrf <input type="hidden" name="status" value="in_progress">
                                        <button class="btn btn-sm btn-outline-info" title="{{ __('portal.staff_tasks.start') }}"><i class="fas fa-play"></i></button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('staff-tasks.status', $task) }}">
                                        @csrf <input type="hidden" name="status" value="done">
                                        <button class="btn btn-sm btn-success" title="{{ __('portal.staff_tasks.mark_done') }}"><i class="fas fa-check"></i> {{ __('portal.staff_tasks.done') }}</button>
                                    </form>
                                @elseif($task->status === 'done')
                                    <span class="text-success" style="font-size:.8rem;"><i class="fas fa-circle-check"></i> {{ $task->completed_at?->translatedFormat('M d') }}</span>
                                    <form method="POST" action="{{ route('staff-tasks.status', $task) }}">
                                        @csrf <input type="hidden" name="status" value="in_progress">
                                        <button class="btn btn-sm btn-outline-secondary" title="{{ __('portal.staff_tasks.reopen') }}"><i class="fas fa-rotate-left"></i></button>
                                    </form>
                                @endif
                                @if($isManager)
                                <form method="POST" action="{{ route('staff-tasks.destroy', $task) }}" onsubmit="return confirm('{{ __('portal.staff_tasks.delete_confirm') }}');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="{{ __('portal.staff_tasks.delete') }}"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
