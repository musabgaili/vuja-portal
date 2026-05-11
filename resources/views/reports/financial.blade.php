@extends('layouts.internal-dashboard')
@section('title', __('portal.reports.financial.title'))

@section('breadcrumbs')
<li class="breadcrumb-item">{{ __('portal.internal.financial_reports') }}</li>
<li class="breadcrumb-item active">{{ __('portal.reports.financial.title') }}</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-content">
                <div class="text-muted small">{{ __('portal.projects_manager.index.total_projects') }}</div>
                <div class="fs-4 fw-bold">{{ number_format($totals['projects']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-content">
                <div class="text-muted small">{{ __('portal.reports.financial.total_budget') }}</div>
                <div class="fs-4 fw-bold text-primary">${{ number_format($totals['budget'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-content">
                <div class="text-muted small">{{ __('portal.reports.financial.total_spent') }}</div>
                <div class="fs-4 fw-bold text-warning">${{ number_format($totals['spent'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-content">
                <div class="text-muted small">{{ __('portal.reports.financial.over_budget_projects') }}</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($totals['over_budget']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-1">{{ __('portal.reports.financial.title') }}</h3>
            <small class="text-muted">{{ __('portal.reports.financial.subtitle') }}</small>
        </div>
        <div class="badge {{ $totals['remaining'] < 0 ? 'bg-danger' : 'bg-success' }}">
            {{ __('portal.reports.financial.total_remaining') }}: ${{ number_format($totals['remaining'], 2) }}
        </div>
    </div>
    <div class="card-content">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">{{ __('portal.projects_manager.index.search') }}</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('portal.projects_manager.index.search_placeholder') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('portal.projects_manager.index.status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('portal.projects_manager.index.all') }}</option>
                    <option value="planning" @selected(request('status') === 'planning')>{{ __('portal.projects_manager.status.planning') }}</option>
                    <option value="quoted" @selected(request('status') === 'quoted')>{{ __('portal.projects_manager.status.quoted') }}</option>
                    <option value="awarded" @selected(request('status') === 'awarded')>{{ __('portal.projects_manager.status.awarded') }}</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>{{ __('portal.projects_manager.status.in_progress') }}</option>
                    <option value="paused" @selected(request('status') === 'paused')>{{ __('portal.projects_manager.status.paused') }}</option>
                    <option value="completed" @selected(request('status') === 'completed')>{{ __('portal.projects_manager.status.completed') }}</option>
                    <option value="lost" @selected(request('status') === 'lost')>{{ __('portal.projects_manager.status.lost') }}</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('portal.projects_manager.status.cancelled') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('portal.reports.financial.budget_health') }}</label>
                <select name="budget_health" class="form-control">
                    <option value="">{{ __('portal.reports.financial.all_health') }}</option>
                    <option value="over_budget" @selected(request('budget_health') === 'over_budget')>{{ __('portal.reports.financial.over_budget') }}</option>
                    <option value="within_budget" @selected(request('budget_health') === 'within_budget')>{{ __('portal.reports.financial.within_budget') }}</option>
                    <option value="no_budget" @selected(request('budget_health') === 'no_budget')>{{ __('portal.reports.financial.no_budget') }}</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('portal.projects_manager.index.search') }}</button>
                <a href="{{ route('reports.financial') }}" class="btn btn-outline-secondary w-100">{{ __('portal.projects_manager.index.clear') }}</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>{{ __('portal.projects_manager.index.col_project') }}</th>
                        <th>{{ __('portal.projects_manager.index.col_client') }}</th>
                        <th>{{ __('portal.projects_manager.index.col_pm') }}</th>
                        <th>{{ __('portal.projects_manager.index.col_status') }}</th>
                        <th>{{ __('portal.projects_manager.expenses.budget') }}</th>
                        <th>{{ __('portal.projects_manager.expenses.spent') }}</th>
                        <th>{{ __('portal.projects_manager.expenses.remaining') }}</th>
                        <th>{{ __('portal.reports.financial.budget_health') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                    <tr>
                        <td>
                            <a href="{{ route('projects.manager.show', $project) }}" class="fw-semibold text-decoration-none">
                                {{ $project->title }}
                            </a>
                        </td>
                        <td>{{ $project->client?->name ?? __('portal.projects_manager.show.no_client') }}</td>
                        <td>{{ $project->projectManager?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $project->getStatusBadgeColor() }}">
                                {{ $project->getStatusLabel() }}
                            </span>
                        </td>
                        <td>${{ number_format((float) ($project->budget ?? 0), 2) }}</td>
                        <td>${{ number_format((float) ($project->spent ?? 0), 2) }}</td>
                        <td class="{{ $project->getBudgetRemaining() < 0 ? 'text-danger fw-semibold' : '' }}">
                            ${{ number_format((float) $project->getBudgetRemaining(), 2) }}
                        </td>
                        <td>
                            @if(is_null($project->budget))
                            <span class="badge bg-secondary">{{ __('portal.reports.financial.no_budget') }}</span>
                            @elseif($project->isOverBudget())
                            <span class="badge bg-danger">{{ __('portal.reports.financial.over_budget') }}</span>
                            @else
                            <span class="badge bg-success">{{ __('portal.reports.financial.within_budget') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">{{ __('portal.reports.financial.empty') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $projects->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
