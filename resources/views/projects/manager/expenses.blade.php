@extends('layouts.internal-dashboard')
@section('title', __('portal.projects_manager.expenses.title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">{{ __('portal.projects_manager.projects') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('projects.manager.show', $project) }}">{{ Str::limit($project->title, 30) }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.projects_manager.expenses.breadcrumb') }}</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h3>{{ __('portal.projects_manager.expenses.heading') }}</h3>
        @php $cur = config('scope.currency', 'SAR'); @endphp
        <div>
            <span class="badge bg-primary">{{ __('portal.projects_manager.expenses.budget') }}: {{ number_format($project->budget ?? 0, 2) }} {{ $cur }}</span>
            <span class="badge bg-warning">{{ __('portal.projects_manager.expenses.spent') }}: {{ number_format($totalExpenses, 2) }} {{ $cur }}</span>
            @if(($committed ?? 0) > 0)
            <span class="badge bg-info" title="{{ __('portal.projects_manager.expenses.committed_hint') }}">{{ __('portal.projects_manager.expenses.committed') }}: {{ number_format($committed, 2) }} {{ $cur }}</span>
            @endif
            <span class="badge bg-{{ $project->isOverBudget() ? 'danger' : 'success' }}">
                {{ __('portal.projects_manager.expenses.remaining') }}: {{ number_format($project->getBudgetRemaining(), 2) }} {{ $cur }}
            </span>
        </div>
    </div>
    <div class="card-content">
        <button class="btn btn-primary mb-3" onclick="showAddExpenseModal()">
            <i class="fas fa-plus"></i> {{ __('portal.projects_manager.expenses.log_expense') }}
        </button>

        @if($expenses->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('portal.projects_manager.expenses.date') }}</th>
                    <th>{{ __('portal.projects_manager.expenses.col_title') }}</th>
                    <th>{{ __('portal.projects_manager.expenses.category') }}</th>
                    <th>{{ __('portal.projects_manager.expenses.amount') }}</th>
                    <th>{{ __('portal.projects_manager.expenses.logged_by') }}</th>
                    <th>{{ __('portal.team.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->translatedFormat('M d, Y') }}</td>
                    <td>
                        <strong>{{ $expense->title }}</strong>
                        @if($expense->description)
                        <br><small class="text-muted">{{ Str::limit($expense->description, 50) }}</small>
                        @endif
                    </td>
                    <td>{{ $expense->category ?? '—' }}</td>
                    <td><strong>{{ number_format($expense->amount, 2) }} {{ $cur }}</strong></td>
                    <td>{{ $expense->loggedBy->name }}</td>
                    <td>
                        @if($expense->receipt_file)
                        <a href="{{ asset('storage/' . $expense->receipt_file) }}" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-file"></i>
                        </a>
                        @endif
                        <form method="POST" action="{{ route('projects.expenses.destroy', $expense) }}" style="display:inline;" onsubmit="return confirm('{{ __('portal.projects_manager.expenses.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $expenses->links('pagination::bootstrap-5') }}
        @else
        <p class="text-muted text-center py-4">{{ __('portal.projects_manager.expenses.empty') }}</p>
        @endif
    </div>
</div>

{{-- Employee/PM spend requests against this project --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>{{ __('portal.projects_manager.expenses.requests_heading') }}</h3>
        <a href="{{ route('spend.approvals') }}" class="btn btn-sm btn-light"><i class="fas fa-inbox"></i> {{ __('portal.spend.approvals') }}</a>
    </div>
    <div class="card-content p-0" style="overflow-x:auto;">
        @if($spendRequests->count())
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.spend.col_title') }}</th>
                <th>{{ __('portal.spend.col_kind') }}</th>
                <th class="text-end">{{ __('portal.spend.col_amount') }}</th>
                <th>{{ __('portal.spend.requested_by') }}</th>
                <th>{{ __('portal.spend.col_status') }}</th>
            </tr></thead>
            <tbody>
            @foreach($spendRequests as $r)
                <tr>
                    <td><strong>{{ $r->title }}</strong>
                        @if($r->isPurchase() && $r->product_url)<a href="{{ $r->product_url }}" target="_blank" rel="noopener" class="text-muted ms-1" title="{{ __('portal.spend.buy_link') }}"><i class="fas fa-link"></i></a>@endif
                        @if($r->receipt_file)<a href="{{ route('spend.receipt', $r) }}" class="text-muted ms-1" title="{{ __('portal.spend.receipt') }}"><i class="fas fa-paperclip"></i></a>@endif
                    </td>
                    <td><span class="badge bg-{{ $r->isPurchase() ? 'primary' : 'secondary' }}">{{ __('portal.spend.type.'.$r->type) }}</span></td>
                    <td class="text-end">{{ number_format($r->effectiveAmount(), 2) }} {{ $r->currency }}</td>
                    <td>{{ $r->requester->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
                        @if($r->isCommitted())<span class="badge bg-info" title="{{ __('portal.projects_manager.expenses.committed_hint') }}">{{ __('portal.projects_manager.expenses.committed') }}</span>@endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <p class="text-muted text-center py-3 mb-0">{{ __('portal.projects_manager.expenses.no_requests') }}</p>
        @endif
    </div>
</div>

<div class="modal fade" id="addExpenseModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.projects_manager.expenses.log_expense') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.expenses.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.expenses.col_title') }} *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.expenses.amount') }} *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.expenses.category') }}</label>
                        <select name="category" class="form-control">
                            <option value="">{{ __('portal.projects_manager.expenses.select') }}</option>
                            <option value="software">{{ __('portal.projects_manager.expenses.category_software') }}</option>
                            <option value="hardware">{{ __('portal.projects_manager.expenses.category_hardware') }}</option>
                            <option value="service">{{ __('portal.projects_manager.expenses.category_service') }}</option>
                            <option value="travel">{{ __('portal.projects_manager.expenses.category_travel') }}</option>
                            <option value="other">{{ __('portal.projects_manager.expenses.category_other') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.expenses.expense_date') }} *</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.expenses.description') }}</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.projects_manager.expenses.receipt_optional') }}</label>
                        <input type="file" name="receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('portal.projects_manager.expenses.log_expense') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function showAddExpenseModal(){new bootstrap.Modal(document.getElementById('addExpenseModal')).show();}
</script>
@endpush

