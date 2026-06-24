@extends('layouts.internal-dashboard')
@section('title', __('portal.spend.my_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.spend.my_title') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-receipt"></i> {{ __('portal.spend.my_title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.spend.my_sub') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($approvalCount > 0)
            <a href="{{ route('spend.approvals') }}" class="btn btn-light"><i class="fas fa-inbox"></i> {{ __('portal.spend.approvals') }} <span class="badge bg-warning">{{ $approvalCount }}</span></a>
        @endif
        <a href="{{ route('spend.create') }}" class="btn" style="background:#fff;color:var(--primary-color);font-weight:600;"><i class="fas fa-plus"></i> {{ __('portal.spend.new_request') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card"><div class="card-content p-0" style="overflow-x:auto;">
    <table class="table mb-0">
        <thead><tr>
            <th>{{ __('portal.spend.col_title') }}</th>
            <th>{{ __('portal.spend.col_kind') }}</th>
            <th class="text-end">{{ __('portal.spend.col_amount') }}</th>
            <th>{{ __('portal.spend.col_for') }}</th>
            <th>{{ __('portal.spend.col_status') }}</th>
            <th>{{ __('portal.spend.col_date') }}</th>
            <th></th>
        </tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td>
                    <a href="{{ route('spend.show', $r) }}" class="fw-bold text-decoration-none">{{ $r->title }}</a>
                    @if($r->links())<i class="fas fa-link text-muted ms-1" title="{{ __('portal.spend.has_links') }}"></i>@endif
                    <span class="text-muted small d-block">{{ trans_choice('portal.spend.items_count', $r->items->count(), ['count' => $r->items->count()]) }}</span>
                </td>
                <td>
                    <span class="badge bg-{{ $r->isPurchase() ? 'primary' : 'secondary' }}">{{ __('portal.spend.type.'.$r->type) }}</span>
                    <span class="badge bg-light text-dark">{{ __('portal.spend.scope.'.$r->scope) }}</span>
                </td>
                <td class="text-end">{{ number_format($r->effectiveAmount(), 2) }} {{ $r->currency }}</td>
                <td>{{ $r->isProject() ? ($r->project->title ?? '—') : ($r->category ?: __('portal.spend.general')) }}</td>
                <td><span class="badge bg-{{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>@if($r->self_recorded)<span class="badge bg-secondary ms-1" title="{{ __('portal.spend.self_recorded_hint') }}"><i class="fas fa-user-shield"></i></span>@endif</td>
                <td><span class="text-muted small">{{ $r->created_at->translatedFormat('M j, Y') }}</span></td>
                <td class="text-end">
                    @if($r->receipts->isNotEmpty())<a href="{{ route('spend.show', $r) }}" class="text-muted me-2" title="{{ __('portal.spend.receipt') }}"><i class="fas fa-paperclip"></i></a>@endif
                    <a href="{{ route('spend.show', $r) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                    @if($r->isPending())
                        <form method="POST" action="{{ route('spend.destroy', $r) }}" class="d-inline" onsubmit="return confirm('{{ __('portal.spend.delete_confirm') }}')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
            @if($r->review_notes)
                <tr><td colspan="7" class="text-muted small" style="border-top:0;padding-top:0;"><i class="fas fa-comment-dots"></i> {{ $r->reviewer?->name }}: {{ $r->review_notes }}</td></tr>
            @endif
        @empty
            <tr><td colspan="7" class="p-0">
                <x-empty-state icon="fa-receipt" :title="__('portal.spend.none_title')" :text="__('portal.spend.none_text')">
                    <a href="{{ route('spend.create') }}" class="btn btn-sm btn-primary mt-3"><i class="fas fa-plus"></i> {{ __('portal.spend.new_request') }}</a>
                </x-empty-state>
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-2">{{ $requests->links() }}</div>
@endsection
