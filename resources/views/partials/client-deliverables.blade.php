{{--
    Client-facing list of deliverables the assigned team shared back.
    Required var: $model (the service request). Renders nothing when empty.
--}}
@php $shared = $model->clientDeliverables; @endphp
@if($shared->count() > 0)
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-box-open"></i> {{ __('portal.service_work.client_deliverables_title') }}</h3>
    </div>
    <div class="card-content">
        @foreach($shared as $d)
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                <div>
                    <a href="{{ route('service-work.download', $d) }}" class="text-decoration-none">
                        <i class="fas fa-download"></i> {{ $d->content ?: $d->file_name }}
                    </a>
                    <small class="text-muted">({{ $d->size_label }})</small>
                    <small class="text-muted d-block">{{ $d->created_at->translatedFormat('M d, Y') }}</small>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
