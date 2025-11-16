<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8fafc;
            padding: 1rem;
        }
        .doc-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .doc-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .doc-header h4 {
            margin: 0;
            font-weight: 600;
        }
        .doc-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
            border-left-color: #764ba2;
        }
        .doc-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        .doc-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        .doc-tag {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }
        .doc-comment {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.875rem;
            color: #64748b;
        }
        .doc-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        .empty-state h5 {
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }
        .modal-content {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.25rem 1.5rem;
        }
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        .modal-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 0.6rem 0.9rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
        }
        .btn-outline-primary:hover {
            background: #667eea;
            border-color: #667eea;
        }
        .btn-outline-warning {
            border-color: #f59e0b;
            color: #f59e0b;
        }
        .btn-outline-warning:hover {
            background: #f59e0b;
            border-color: #f59e0b;
            color: white;
        }
        .btn-outline-danger {
            border-color: #ef4444;
            color: #ef4444;
        }
        .btn-outline-danger:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
        }
    </style>
</head>
<body>
    <div class="doc-container">
        <div class="doc-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-folder-open"></i> Project Documents</h4>
                <button class="btn btn-light" onclick="showUploadModal()">
                    <i class="fas fa-plus"></i> Upload
                </button>
            </div>
        </div>

        @forelse($documents as $doc)
        <div class="doc-card">
            <div class="d-flex justify-content-between align-items-start">
                <div style="flex: 1;">
                    <div class="doc-title">
                        <i class="fas fa-file-{{ $doc->file_type === 'pdf' ? 'pdf text-danger' : 'alt text-primary' }}"></i>
                        {{ $doc->title }}
                    </div>
                    <div class="doc-meta-row">
                        <span class="doc-tag" style="background: {{ match($doc->tag) { 'initial' => '#a855f7', 'design' => '#3b82f6', 'development' => '#10b981', 'final' => '#f59e0b', default => '#64748b' } }};">
                            {{ ucfirst($doc->tag) }}
                        </span>
                        <span><i class="fas fa-user"></i> {{ $doc->uploadedBy->name }}</span>
                        <span><i class="fas fa-calendar"></i> {{ $doc->created_at->format('M d, Y') }}</span>
                        <span><i class="fas fa-file"></i> {{ number_format($doc->file_size / 1024, 1) }} KB</span>
                    </div>
                    @if($doc->comment)
                    <div class="doc-comment">
                        <i class="fas fa-comment"></i> {{ $doc->comment }}
                    </div>
                    @endif
                </div>
                <div class="doc-actions">
                    <a href="{{ route('projects.client.documents.download', $doc) }}" class="btn btn-outline-primary btn-icon" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                    @if($doc->uploaded_by === auth()->id() || auth()->user()->isManager())
                    <button class="btn btn-outline-warning btn-icon" onclick="editDocument({{ $doc->id }}, '{{ $doc->title }}', '{{ $doc->tag }}', '{{ addslashes($doc->comment) }}')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('projects.documents.destroy', $doc) }}" style="display: inline;" onsubmit="return confirm('Delete this document?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-icon" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>No documents uploaded yet</h5>
            <p>Upload your first document to get started</p>
            <button class="btn btn-primary" onclick="showUploadModal()">
                <i class="fas fa-upload"></i> Upload Document
            </button>
        </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-upload"></i> Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('projects.client.documents.store', $project) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tag *</label>
                            <select name="tag" class="form-control" required>
                                <option value="">Choose...</option>
                                <option value="initial">Initial</option>
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="final">Final</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File *</label>
                            <input type="file" name="file" class="form-control" required>
                            <small class="text-muted">Max: 20MB</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-edit"></i> Update Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tag *</label>
                            <select name="tag" id="edit_tag" class="form-control" required>
                                <option value="initial">Initial</option>
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="final">Final</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Replace File</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" id="edit_comment" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showUploadModal() {
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        }

        function editDocument(id, title, tag, comment) {
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_tag').value = tag;
            document.getElementById('edit_comment').value = comment || '';
            document.getElementById('editForm').action = `/internal/projects/documents/${id}`;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
