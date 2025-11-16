@extends('layouts.internal-dashboard')
@section('title', 'Project Expenses')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('projects.manager.index') }}">Projects</a></li>
<li class="breadcrumb-item"><a href="{{ route('projects.manager.show', $project) }}">{{ Str::limit($project->title, 30) }}</a></li>
<li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h3>Project Expenses</h3>
        <div>
            <span class="badge bg-primary">Budget: ${{ number_format($project->budget ?? 0, 2) }}</span>
            <span class="badge bg-warning">Spent: ${{ number_format($totalExpenses, 2) }}</span>
            <span class="badge bg-{{ $project->isOverBudget() ? 'danger' : 'success' }}">
                Remaining: ${{ number_format($project->getBudgetRemaining(), 2) }}
            </span>
        </div>
    </div>
    <div class="card-content">
        <button class="btn btn-primary mb-3" onclick="showAddExpenseModal()">
            <i class="fas fa-plus"></i> Log Expense
        </button>

        @if($expenses->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Logged By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                    <td>
                        <strong>{{ $expense->title }}</strong>
                        @if($expense->description)
                        <br><small class="text-muted">{{ Str::limit($expense->description, 50) }}</small>
                        @endif
                    </td>
                    <td>{{ $expense->category ?? '—' }}</td>
                    <td><strong>${{ number_format($expense->amount, 2) }}</strong></td>
                    <td>{{ $expense->loggedBy->name }}</td>
                    <td>
                        @if($expense->receipt_file)
                        <a href="{{ asset('storage/' . $expense->receipt_file) }}" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-file"></i>
                        </a>
                        @endif
                        <form method="POST" action="{{ route('projects.expenses.destroy', $expense) }}" style="display:inline;" onsubmit="return confirm('Delete this expense?')">
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
        <p class="text-muted text-center py-4">No expenses logged yet.</p>
        @endif
    </div>
</div>

<div class="modal fade" id="addExpenseModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Log Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.expenses.store', $project) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Amount ($) *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">Select...</option>
                            <option value="software">Software</option>
                            <option value="hardware">Hardware</option>
                            <option value="service">Service</option>
                            <option value="travel">Travel</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Expense Date *</label>
                        <input type="date" name="expense_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Receipt (Optional)</label>
                        <input type="file" name="receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Log Expense
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

