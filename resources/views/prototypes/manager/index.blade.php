@extends('layouts.internal-dashboard')
@section('title', __('portal.prototypes.queue_title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-cube"></i> {{ __('portal.prototypes.queue_title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.prototypes.queue_subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.prototypes.field.title') }}</th>
                <th>{{ __('portal.prototypes.client') }}</th>
                <th>{{ __('portal.prototypes.field.category') }}</th>
                <th>{{ __('portal.prototypes.assigned_to') }}</th>
                <th>{{ __('portal.prototypes.field.status') }}</th>
                <th>{{ __('portal.prototypes.submitted_on') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @forelse($prototypes as $p)
                    <tr>
                        <td><strong>{{ $p->title }}</strong>@if($p->files_count ?? $p->files()->count())<i class="fas fa-paperclip text-muted ms-1"></i>@endif</td>
                        <td>{{ $p->user->name ?? '—' }}</td>
                        <td>{{ $p->category ?: '—' }}</td>
                        <td>{{ $p->assignedTo->name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $p->getStatusBadgeColor() }}">{{ $p->getStatusLabel() }}</span></td>
                        <td><small class="text-muted">{{ $p->created_at->translatedFormat('M j, Y') }}</small></td>
                        <td class="text-end"><a href="{{ route('prototypes.manager.show', $p) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> {{ __('portal.internal.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center py-4">{{ __('portal.prototypes.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $prototypes->links() }}</div>
@endsection
