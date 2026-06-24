@extends('layouts.internal-dashboard')
@section('title', $spend->title)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('spend.index') }}">{{ __('portal.spend.my_title') }}</a></li>
<li class="breadcrumb-item active">{{ $spend->title }}</li>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="card mb-3">
    <div class="card-content">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2 style="margin:0;font-size:1.3rem;">{{ $spend->title }}</h2>
                <div class="mt-1">
                    <span class="badge bg-{{ $spend->isPurchase() ? 'primary' : 'secondary' }}">{{ __('portal.spend.type.'.$spend->type) }}</span>
                    <span class="badge bg-light text-dark">{{ __('portal.spend.scope.'.$spend->scope) }}</span>
                    <span class="badge bg-{{ $spend->statusColor() }}">{{ $spend->statusLabel() }}</span>
                    @if($spend->self_recorded)<span class="badge bg-secondary" title="{{ __('portal.spend.self_recorded_hint') }}"><i class="fas fa-user-shield"></i> {{ __('portal.spend.self_recorded') }}</span>@endif
                </div>
                <div class="text-muted small mt-2">
                    <i class="fas fa-user"></i> {{ $spend->requester->name ?? '—' }} ·
                    {{ $spend->isProject() ? ($spend->project->title ?? '—') : ($spend->category ?: __('portal.spend.general')) }} ·
                    {{ $spend->created_at->translatedFormat('M j, Y') }}
                </div>
            </div>
            <div class="text-end">
                <div style="font-size:1.6rem;font-weight:800;color:var(--primary-color);">{{ number_format($spend->effectiveAmount(), 2) }} {{ $spend->currency }}</div>
                @if($spend->isCompleted() && $spend->actual_amount !== null && (float) $spend->actual_amount !== (float) $spend->amount)
                    <small class="text-muted">{{ __('portal.spend.estimated') }}: {{ number_format((float) $spend->amount, 2) }} {{ $spend->currency }}</small>
                @endif
                @if($canEdit)
                    <div class="mt-2">
                        <a href="{{ route('spend.edit', $spend) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i> {{ __('portal.spend.edit') }}</a>
                        <form method="POST" action="{{ route('spend.destroy', $spend) }}" class="d-inline" onsubmit="return confirm('{{ __('portal.spend.delete_confirm') }}')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        @if($spend->description)<p class="mt-3 mb-0">{{ $spend->description }}</p>@endif
    </div>
</div>

{{-- Line items --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-list"></i> {{ __('portal.spend.items') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.spend.item_desc') }}</th>
                <th class="text-end">{{ __('portal.spend.quantity') }}</th>
                <th class="text-end">{{ __('portal.spend.unit_amount') }}</th>
                <th class="text-end">{{ __('portal.spend.line_total') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @foreach($spend->items as $it)
                <tr>
                    <td>{{ $it->description }}</td>
                    <td class="text-end">{{ $it->quantity }}</td>
                    <td class="text-end">{{ number_format((float) $it->unit_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($it->lineTotal(), 2) }}</td>
                    <td class="text-end">@if($it->product_url)<a href="{{ $it->product_url }}" target="_blank" rel="noopener" title="{{ __('portal.spend.item_link') }}"><i class="fas fa-link"></i></a>@endif</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="3" class="text-end">{{ __('portal.spend.total') }}</td><td class="text-end">{{ number_format($spend->itemsTotal(), 2) }} {{ $spend->currency }}</td><td></td></tr></tfoot>
        </table>
    </div>
</div>

{{-- Receipts / attachments --}}
@if($spend->receipts->isNotEmpty() || $spend->actualReceipts->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-paperclip"></i> {{ __('portal.spend.receipts') }}</span></div>
    <div class="card-content">
        @foreach($spend->receipts as $f)
            <a href="{{ route('spend.file', $f) }}" class="btn btn-sm btn-outline-secondary mb-1"><i class="fas fa-file"></i> {{ $f->displayName() }}</a>
        @endforeach
        @foreach($spend->actualReceipts as $f)
            <a href="{{ route('spend.file', $f) }}" class="btn btn-sm btn-outline-info mb-1"><i class="fas fa-file-invoice-dollar"></i> {{ $f->displayName() }}</a>
        @endforeach
    </div>
</div>
@endif

{{-- Review / status timeline --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-clock-rotate-left"></i> {{ __('portal.spend.history') }}</span></div>
    <div class="card-content">
        <ul class="mb-0" style="list-style:none;padding-{{ app()->getLocale()==='ar' ? 'right' : 'left' }}:0;">
            <li class="mb-1"><i class="fas fa-paper-plane text-muted"></i> {{ __('portal.spend.submitted_on', ['date' => $spend->created_at->translatedFormat('M j, Y g:i A')]) }}</li>
            @if($spend->reviewed_at)
                <li class="mb-1"><i class="fas fa-gavel text-muted"></i> {{ __('portal.spend.reviewed_by', ['name' => $spend->reviewer->name ?? '—', 'date' => $spend->reviewed_at->translatedFormat('M j, Y g:i A')]) }} — {{ $spend->statusLabel() }}</li>
                @if($spend->review_notes)<li class="mb-1 text-muted" style="padding-{{ app()->getLocale()==='ar' ? 'right' : 'left' }}:1.5rem;"><i class="fas fa-comment-dots"></i> {{ $spend->review_notes }}</li>@endif
            @endif
            @if($spend->completed_at)
                <li class="mb-1"><i class="fas fa-circle-check text-success"></i> {{ __('portal.spend.completed_on', ['date' => $spend->completed_at->translatedFormat('M j, Y g:i A')]) }}</li>
            @endif
        </ul>
    </div>
</div>

{{-- Actions --}}
@if($canApprove)
<div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-gavel"></i> {{ __('portal.spend.decision') }}</span></div>
<div class="card-content d-flex gap-2 flex-wrap">
    <form method="POST" action="{{ route('spend.approve', $spend) }}" class="d-flex gap-1 flex-grow-1">@csrf
        <input type="text" name="review_notes" class="form-control form-control-sm" placeholder="{{ __('portal.spend.notes_ph') }}">
        <button class="btn btn-sm btn-success text-nowrap"><i class="fas fa-check"></i> {{ __('portal.spend.approve') }}</button>
    </form>
    <form method="POST" action="{{ route('spend.reject', $spend) }}" onsubmit="return confirm('{{ __('portal.spend.reject_confirm') }}')">@csrf
        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-ban"></i> {{ __('portal.spend.reject') }}</button>
    </form>
</div></div>
@endif

@if($canFulfil && $spend->isApproved())
<div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-truck"></i> {{ __('portal.spend.fulfilment') }}</span></div>
<div class="card-content">
    @if($spend->isPurchase())
        <form method="POST" action="{{ route('spend.purchase', $spend) }}" enctype="multipart/form-data" class="row g-2 align-items-end">@csrf
            <div class="col-md-4"><label class="form-label small mb-0">{{ __('portal.spend.actual_cost') }} ({{ $spend->currency }})</label><input type="number" step="0.01" min="0" name="actual_amount" class="form-control form-control-sm" value="{{ $spend->amount }}" required></div>
            <div class="col-md-5"><label class="form-label small mb-0">{{ __('portal.spend.actual_receipt') }}</label><input type="file" name="actual_receipts[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" multiple></div>
            <div class="col-md-3"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-cart-shopping"></i> {{ __('portal.spend.mark_purchased') }}</button></div>
        </form>
    @else
        <form method="POST" action="{{ route('spend.reimburse', $spend) }}" onsubmit="return confirm('{{ __('portal.spend.reimburse_confirm') }}')">@csrf
            <button class="btn btn-sm btn-success"><i class="fas fa-hand-holding-dollar"></i> {{ __('portal.spend.mark_reimbursed') }}</button>
            <small class="text-muted ms-2">{{ __('portal.spend.already_in_ledger') }}</small>
        </form>
    @endif
</div></div>
@endif
@endsection
