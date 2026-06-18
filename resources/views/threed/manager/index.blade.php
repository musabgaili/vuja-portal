@extends('layouts.internal-dashboard')
@section('title', __('portal.threed.queue_title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-cubes"></i> {{ __('portal.threed.queue_title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.threed.queue_subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('threed.manager.index') }}" class="btn btn-sm {{ !$activeType ? 'btn-primary' : 'btn-outline-secondary' }}">{{ __('portal.threed.filter_all') }}</a>
    <a href="{{ route('threed.manager.index', ['type' => 'printing']) }}" class="btn btn-sm {{ $activeType === 'printing' ? 'btn-primary' : 'btn-outline-secondary' }}"><i class="fas fa-print"></i> {{ __('portal.threed.type.printing') }}</a>
    <a href="{{ route('threed.manager.index', ['type' => 'design']) }}" class="btn btn-sm {{ $activeType === 'design' ? 'btn-primary' : 'btn-outline-secondary' }}"><i class="fas fa-pen-ruler"></i> {{ __('portal.threed.type.design') }}</a>
</div>

<div class="card">
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.threed.field.title') }}</th>
                <th>{{ __('portal.threed.field.type') }}</th>
                <th>{{ __('portal.threed.client') }}</th>
                <th>{{ __('portal.threed.assigned_to') }}</th>
                <th>{{ __('portal.threed.field.status') }}</th>
                <th>{{ __('portal.threed.submitted_on') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td><strong>{{ $r->title }}</strong>@if($r->files_count ?? $r->files()->count())<i class="fas fa-paperclip text-muted ms-1"></i>@endif</td>
                        <td><span class="badge bg-{{ $r->isPrinting() ? 'info' : 'secondary' }}">{{ $r->typeLabel() }}</span></td>
                        <td>{{ $r->user->name ?? '—' }}</td>
                        <td>{{ $r->assignedTo->name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $r->getStatusBadgeColor() }}">{{ $r->getStatusLabel() }}</span></td>
                        <td><small class="text-muted">{{ $r->created_at->translatedFormat('M j, Y') }}</small></td>
                        <td class="text-end"><a href="{{ route('threed.manager.show', $r) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> {{ __('portal.internal.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center py-4">{{ __('portal.threed.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
