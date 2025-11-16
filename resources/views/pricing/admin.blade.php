@extends('layouts.internal-dashboard')
@section('title', 'Pricing Rules Management')

@section('breadcrumbs')
<li class="breadcrumb-item">Quote System</li>
<li class="breadcrumb-item active">Pricing Admin</li>
@endsection

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Inter', sans-serif; }
.admin-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
}
.admin-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}
</style>

<div class="admin-header">
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fas fa-cogs"></i> Pricing Rules Management
        </h1>
        <p class="opacity-90">Define and manage pricing rules for the quotation tool</p>
    </div>
</div>

<!-- Add Rule Form -->
<div class="admin-card">
    <h3 class="text-xl font-semibold mb-4" style="color: #1e293b;">
        <i class="fas fa-plus-circle"></i> Add New Pricing Rule
    </h3>
    
    <form method="POST" action="{{ route('pricing.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Item *</label>
                    <input type="text" name="item" class="form-control" placeholder="e.g., 3D Design" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Rate ($) *</label>
                    <input type="number" name="rate" class="form-control" step="0.01" min="0" placeholder="100.00" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Unit *</label>
                    <input type="text" name="unit" class="form-control" placeholder="hour" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Level *</label>
                    <input type="text" name="level" class="form-control" placeholder="expert" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Note *</label>
                    <input type="text" name="note" class="form-control" placeholder="Details..." required>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Rule
        </button>
    </form>
</div>

<!-- Rules List -->
<div class="admin-card">
    <h3 class="text-xl font-semibold mb-4" style="color: #1e293b;">
        <i class="fas fa-list"></i> All Pricing Rules ({{ $rules->count() }})
    </h3>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Rate</th>
                    <th>Unit</th>
                    <th>Level</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                <tr>
                    <td><strong>{{ $rule->item }}</strong></td>
                    <td><strong class="text-success">${{ number_format($rule->rate, 2) }}</strong></td>
                    <td><span class="badge bg-secondary">{{ $rule->unit }}</span></td>
                    <td><span class="badge bg-info">{{ $rule->level }}</span></td>
                    <td><small class="text-muted">{{ Str::limit($rule->note, 60) }}</small></td>
                    <td>
                        @if($rule->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" onclick="editRule({{ $rule->id }}, '{{ $rule->item }}', {{ $rule->rate }}, '{{ $rule->unit }}', '{{ $rule->level }}', '{{ addslashes($rule->note) }}', {{ $rule->is_active ? 'true' : 'false' }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('pricing.destroy', $rule) }}" style="display: inline;" onsubmit="return confirm('Delete this rule?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No pricing rules yet. Add your first rule above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Pricing Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editRuleForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Item *</label>
                                <input type="text" name="item" id="edit_item" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Level *</label>
                                <input type="text" name="level" id="edit_level" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rate ($) *</label>
                                <input type="number" name="rate" id="edit_rate" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit *</label>
                                <input type="text" name="unit" id="edit_unit" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Note *</label>
                        <textarea name="note" id="edit_note" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Active (visible to employees)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editRule(id, item, rate, unit, level, note, isActive) {
    document.getElementById('edit_item').value = item;
    document.getElementById('edit_rate').value = rate;
    document.getElementById('edit_unit').value = unit;
    document.getElementById('edit_level').value = level;
    document.getElementById('edit_note').value = note;
    document.getElementById('edit_is_active').checked = isActive;
    document.getElementById('editRuleForm').action = `/internal/pricing-rules/${id}`;
    new bootstrap.Modal(document.getElementById('editRuleModal')).show();
}
</script>
@endpush


