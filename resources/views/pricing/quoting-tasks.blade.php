@extends('layouts.internal-dashboard')
@section('title', __('portal.pricing.quoting_tasks_title'))

@section('breadcrumbs')
<li class="breadcrumb-item">{{ __('portal.pricing.quote_system') }}</li>
<li class="breadcrumb-item active">{{ __('portal.pricing.quoting_tasks_breadcrumb') }}</li>
@endsection

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Inter', sans-serif; }
.quote-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(99, 102, 241, 0.3);
}
.project-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border-left: 4px solid #6366f1;
    transition: all 0.3s;
}
.project-card:hover {
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.2);
    transform: translateY(-2px);
}
.quote-badge {
    background: linear-gradient(135deg, #10b981 0%, #2C3F43 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<div class="quote-header">
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fas fa-file-invoice-dollar"></i> {{ __('portal.pricing.quoting_tasks_heading') }}
        </h1>
        <p class="opacity-90">{{ __('portal.pricing.quoting_tasks_subtitle') }}</p>
    </div>
</div>

@if($projects->count() > 0)
<div class="row">
    @foreach($projects as $project)
    <div class="col-md-6 col-lg-4">
        <div class="project-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1"><strong>{{ $project->title }}</strong></h5>
                    <small class="text-muted">#{{ $project->id }}</small>
                </div>
                <span class="badge bg-warning">{{ __('portal.pricing.planning') }}</span>
            </div>

            @if($project->client)
            <div class="mb-2">
                <i class="fas fa-user text-muted"></i>
                <strong class="ms-1">{{ $project->client->name }}</strong>
                <br>
                <small class="text-muted ms-3">{{ $project->client->email }}</small>
            </div>
            @endif

            @if($project->description)
            <p class="text-muted small mb-3">{{ Str::limit($project->description, 100) }}</p>
            @endif

            @if($project->quote_file)
            <div class="mb-3 p-2 bg-light rounded">
                <i class="fas fa-file-pdf text-danger"></i>
                <span class="ms-2 small">{{ __('portal.pricing.quote_uploaded') }}</span>
                @if($project->quotedBy)
                <br><small class="text-muted ms-3">{{ __('portal.pricing.by') }} {{ $project->quotedBy->name }}</small>
                @endif
                @if($project->quoted_at)
                <br><small class="text-muted ms-3">{{ $project->quoted_at->format('M d, Y H:i') }}</small>
                @endif
            </div>
            @endif

            <div class="d-grid gap-2">
                <a href="{{ route('projects.manager.show', $project) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> {{ __('portal.pricing.view_project') }}
                </a>
                @if($project->quote_file)
                <button class="btn btn-sm btn-success" onclick="updateQuote({{ $project->id }})">
                    <i class="fas fa-edit"></i> {{ __('portal.pricing.update_quote') }}
                </button>
                <a href="{{ route('projects.quote.download', $project) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-download"></i> {{ __('portal.pricing.download') }}
                </a>
                @else
                <button class="btn btn-sm btn-primary" onclick="uploadQuote({{ $project->id }})">
                    <i class="fas fa-upload"></i> {{ __('portal.pricing.upload_quote') }}
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $projects->links('pagination::bootstrap-5') }}
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
    <h4>{{ __('portal.pricing.empty_planning_projects_title') }}</h4>
    <p class="text-muted">{{ __('portal.pricing.empty_planning_projects_body') }}</p>
</div>
@endif

<!-- Upload Quote Modal -->
<div class="modal fade" id="uploadQuoteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalTitle">Upload Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="quoteForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('portal.pricing.quote_document') }} *</label>
                        <input type="file" name="quote_file" class="form-control" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted">{{ __('portal.pricing.quote_document_help') }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ __('portal.pricing.notes_optional') }}</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('portal.pricing.notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> {{ __('portal.pricing.upload_quote') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function uploadQuote(projectId) {
    document.getElementById('modalTitle').textContent = @js(__('portal.pricing.upload_quote'));
    document.getElementById('quoteForm').action = `/internal/projects/${projectId}/upload-quote`;
    new bootstrap.Modal(document.getElementById('uploadQuoteModal')).show();
}

function updateQuote(projectId) {
    document.getElementById('modalTitle').textContent = @js(__('portal.pricing.update_quote'));
    document.getElementById('quoteForm').action = `/internal/projects/${projectId}/upload-quote`;
    new bootstrap.Modal(document.getElementById('uploadQuoteModal')).show();
}
</script>
@endpush

