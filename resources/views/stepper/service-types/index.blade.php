@extends('layouts.internal-dashboard')

@section('title', 'Service Request Types')
@section('page-title', 'Service Request Types Management')

@section('content')
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-content">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('stepper.service-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Service Type
            </a>
            <a href="{{ route('stepper.client.index') }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-eye"></i> Preview Client View
            </a>
        </div>
    </div>
</div>

<!-- Service Types List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Service Request Types</h3>
        <div class="d-flex gap-2">
            <select class="form-control" style="width: auto;" onchange="filterByStatus(this.value)">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
    <div class="card-content">
        @if($serviceTypes->count() > 0)
            <div class="service-types-list">
                @foreach($serviceTypes as $serviceType)
                <div class="service-type-item d-flex align-center justify-between mb-3 p-4 rounded-lg" 
                     style="background: var(--bg-tertiary); border-left: 4px solid {{ $serviceType->color }};">
                    <div class="d-flex align-center">
                        <div class="service-type-icon" style="background: {{ $serviceType->color }};">
                            <i class="{{ $serviceType->icon }}"></i>
                        </div>
                        <div class="service-type-info">
                            <h4 class="mb-1">{{ $serviceType->name }}</h4>
                            <p class="text-muted mb-1">
                                <strong>Slug:</strong> {{ $serviceType->slug }} • 
                                <strong>Steps:</strong> {{ $serviceType->steps->count() }} • 
                                <strong>Created:</strong> {{ $serviceType->created_at->diffForHumans() }}
                            </p>
                            @if($serviceType->description)
                            <p class="service-type-description">{{ Str::limit($serviceType->description, 150) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="service-type-actions d-flex align-center gap-2">
                        <span class="status-badge {{ $serviceType->is_active ? 'success' : 'secondary' }}">
                            {{ $serviceType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <a href="{{ route('stepper.service-types.show', $serviceType) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('stepper.service-types.edit', $serviceType) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('stepper.service-types.destroy', $serviceType) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this service type?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-error btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $serviceTypes->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                    <h4>No Service Types</h4>
                    <p class="text-muted">You haven't created any service request types yet.</p>
                    <a href="{{ route('stepper.service-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First Service Type
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.service-type-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--space-md);
    color: white;
    font-size: var(--font-size-lg);
}

.status-badge {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.secondary {
    background: #f1f5f9;
    color: #475569;
}

.text-muted {
    color: var(--gray-500);
}

.gap-2 {
    gap: var(--space-sm);
}

.flex-wrap {
    flex-wrap: wrap;
}

.empty-state {
    padding: var(--space-2xl);
}

.empty-state i {
    opacity: 0.5;
}

.service-type-description {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    line-height: 1.4;
}
</style>
@endpush

@push('scripts')
<script>
function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}
</script>
@endpush

