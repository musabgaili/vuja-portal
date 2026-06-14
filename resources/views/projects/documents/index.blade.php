<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('portal.projects_documents.page_title') }}</title>
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
            background: linear-gradient(135deg, #0F969C 0%, #0C7075 100%);
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
            border-left: 4px solid #0F969C;
            transition: all 0.3s ease;
        }
        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
            border-left-color: #0C7075;
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
            background: linear-gradient(135deg, #0F969C 0%, #0C7075 100%);
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
            border-color: #0F969C;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0F969C 0%, #0C7075 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .btn-outline-primary {
            border-color: #0F969C;
            color: #0F969C;
        }
        .btn-outline-primary:hover {
            background: #0F969C;
            border-color: #0F969C;
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
                <h4><i class="fas fa-folder-open"></i> {{ __('portal.projects_documents.heading') }}</h4>
                <button class="btn btn-light" onclick="showUploadModal()">
                    <i class="fas fa-plus"></i> {{ __('portal.projects_documents.upload_btn') }}
                </button>
            </div>
        </div>

        <div id="pageFeedback" class="mb-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') ?? $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif
        </div>

        <div id="embeddedToastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

        @forelse($documents as $doc)
        <div class="doc-card">
            <div class="d-flex justify-content-between align-items-start">
                <div style="flex: 1;">
                    <div class="doc-title">
                        <i class="fas fa-file-{{ $doc->file_type === 'pdf' ? 'pdf text-danger' : 'alt text-primary' }}"></i>
                        {{ $doc->title }}
                    </div>
                    <div class="doc-meta-row">
                        <span class="doc-tag" style="background: {{ match($doc->tag) { 'initial' => '#0C7075', 'design' => '#3b82f6', 'development' => '#10b981', 'final' => '#f59e0b', default => '#64748b' } }};">
                            {{ __('portal.projects_documents.tag_' . $doc->tag) }}
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
                    <a href="{{ route('projects.client.documents.download', $doc) }}" class="btn btn-outline-primary btn-icon" title="{{ __('portal.projects_documents.download_title') }}">
                        <i class="fas fa-download"></i>
                    </a>
                    @if($doc->uploaded_by === auth()->id() || auth()->user()->isManager())
                    <button class="btn btn-outline-warning btn-icon" onclick="editDocument(@js($doc->getRouteKey()), @js($doc->title), @js($doc->tag), @js($doc->comment))" title="{{ __('portal.projects_documents.edit_title') }}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('projects.documents.destroy', $doc) }}" class="js-document-delete" style="display: inline;" data-confirm="{{ __('portal.projects_documents.delete_confirm') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-icon" title="{{ __('portal.projects_documents.delete_title') }}">
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
            <h5>{{ __('portal.projects_documents.empty_title') }}</h5>
            <p>{{ __('portal.projects_documents.empty_body') }}</p>
            <button class="btn btn-primary" onclick="showUploadModal()">
                <i class="fas fa-upload"></i> {{ __('portal.projects_documents.empty_upload') }}
            </button>
        </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="fas fa-upload"></i> {{ __('portal.projects_documents.upload_modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="uploadForm" action="{{ route('projects.client.documents.store', $project) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div id="uploadFormErrors" class="alert alert-danger d-none" role="alert"></div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('portal.projects_documents.label_title') }} *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('portal.projects_documents.label_tag') }} *</label>
                            <select name="tag" class="form-control" required>
                                <option value="">{{ __('portal.projects_documents.tag_choose') }}</option>
                                <option value="initial">{{ __('portal.projects_documents.tag_initial') }}</option>
                                <option value="design">{{ __('portal.projects_documents.tag_design') }}</option>
                                <option value="development">{{ __('portal.projects_documents.tag_development') }}</option>
                                <option value="final">{{ __('portal.projects_documents.tag_final') }}</option>
                                <option value="other">{{ __('portal.projects_documents.tag_other') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('portal.projects_documents.label_file') }} *</label>
                            <input type="file" name="file" class="form-control" required>
                            <small class="text-muted">{{ __('portal.projects_documents.max_file_hint') }}</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('portal.projects_documents.label_comment') }}</label>
                            <textarea name="comment" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.projects_documents.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('portal.projects_documents.upload') }}</button>
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
                    <h5><i class="fas fa-edit"></i> {{ __('portal.projects_documents.edit_modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div id="editFormErrors" class="alert alert-danger d-none" role="alert"></div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('portal.projects_documents.label_title') }} *</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('portal.projects_documents.label_tag') }} *</label>
                            <select name="tag" id="edit_tag" class="form-control" required>
                                <option value="initial">{{ __('portal.projects_documents.tag_initial') }}</option>
                                <option value="design">{{ __('portal.projects_documents.tag_design') }}</option>
                                <option value="development">{{ __('portal.projects_documents.tag_development') }}</option>
                                <option value="final">{{ __('portal.projects_documents.tag_final') }}</option>
                                <option value="other">{{ __('portal.projects_documents.tag_other') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('portal.projects_documents.replace_file') }}</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('portal.projects_documents.label_comment') }}</label>
                            <textarea name="comment" id="edit_comment" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.projects_documents.cancel') }}</button>
                        <button type="submit" class="btn btn-warning">{{ __('portal.projects_documents.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadModalEl = document.getElementById('uploadModal');
        const editModalEl = document.getElementById('editModal');
        const pageFeedbackEl = document.getElementById('pageFeedback');
        const embeddedToastContainer = document.getElementById('embeddedToastContainer');

        function showUploadModal() {
            bootstrap.Modal.getOrCreateInstance(uploadModalEl).show();
        }

        function editDocument(id, title, tag, comment) {
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_tag').value = tag;
            document.getElementById('edit_comment').value = comment || '';
            document.getElementById('editForm').action = `/internal/projects/documents/${id}`;
            clearFormErrors(document.getElementById('editFormErrors'));
            bootstrap.Modal.getOrCreateInstance(editModalEl).show();
        }

        function clearFormErrors(container) {
            container.classList.add('d-none');
            container.innerHTML = '';
        }

        function renderFormErrors(container, messages) {
            const items = messages.map((message) => `<li>${message}</li>`).join('');
            container.innerHTML = `<ul class="mb-0 ps-3">${items}</ul>`;
            container.classList.remove('d-none');
        }

        function showPageFeedback(message, type = 'success') {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            pageFeedbackEl.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            `;
        }

        function showEmbeddedToast(message, type = 'success') {
            if (window.parent && window.parent !== window && typeof window.parent.showAppToast === 'function') {
                window.parent.showAppToast(message, type);
            }

            const className = type === 'success'
                ? 'text-bg-success'
                : type === 'warning'
                    ? 'text-bg-warning'
                    : 'text-bg-danger';

            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center ${className} border-0 mb-2`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                </div>
            `;

            embeddedToastContainer.appendChild(toastEl);
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
            toast.show();
        }

        async function submitDocumentForm(form, options = {}) {
            const {
                errorContainer,
                modalEl,
                resetForm = false
            } = options;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.innerHTML : null;

            if (errorContainer) {
                clearFormErrors(errorContainer);
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>${originalText}`;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(form)
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && errorContainer) {
                        const messages = Object.values(payload.errors || {}).flat();
                        const errorMessages = messages.length ? messages : [payload.message || 'Please review the form and try again.'];
                        renderFormErrors(errorContainer, errorMessages);
                        showEmbeddedToast(errorMessages[0], 'error');
                        return;
                    }

                    throw new Error(payload.message || 'Request failed.');
                }

                showPageFeedback(payload.message, 'success');
                showEmbeddedToast(payload.message, 'success');

                if (modalEl) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                if (resetForm) {
                    form.reset();
                }

                window.setTimeout(() => {
                    window.location.reload();
                }, 600);
            } catch (error) {
                const message = error.message || 'Request failed.';

                if (errorContainer) {
                    renderFormErrors(errorContainer, [message]);
                } else {
                    showPageFeedback(message, 'error');
                }

                showEmbeddedToast(message, 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', function (event) {
            event.preventDefault();
            submitDocumentForm(this, {
                errorContainer: document.getElementById('uploadFormErrors'),
                modalEl: uploadModalEl,
                resetForm: true
            });
        });

        document.getElementById('editForm').addEventListener('submit', function (event) {
            event.preventDefault();
            submitDocumentForm(this, {
                errorContainer: document.getElementById('editFormErrors'),
                modalEl: editModalEl
            });
        });

        document.querySelectorAll('.js-document-delete').forEach((form) => {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (!confirm(form.dataset.confirm)) {
                    return;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'Request failed.');
                    }

                    showPageFeedback(payload.message, 'success');
                    showEmbeddedToast(payload.message, 'success');

                    window.setTimeout(() => {
                        window.location.reload();
                    }, 400);
                } catch (error) {
                    const message = error.message || 'Request failed.';
                    showPageFeedback(message, 'error');
                    showEmbeddedToast(message, 'error');
                }
            });
        });

        uploadModalEl.addEventListener('hidden.bs.modal', () => {
            document.getElementById('uploadForm').reset();
            clearFormErrors(document.getElementById('uploadFormErrors'));
        });

        editModalEl.addEventListener('hidden.bs.modal', () => {
            clearFormErrors(document.getElementById('editFormErrors'));
        });
    </script>
</body>
</html>
