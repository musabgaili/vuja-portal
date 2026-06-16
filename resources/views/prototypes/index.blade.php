@extends('layouts.dashboard')

@section('title', __('portal.prototypes.my_title'))
@section('page-title', __('portal.prototypes.my_title'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cube"></i> {{ __('portal.prototypes.my_title') }}</h3>
        <a href="{{ route('prototypes.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('portal.prototypes.new') }}</a>
    </div>
    <div class="card-content p-0">
        @if(session('success'))<div class="alert alert-success m-3">{{ session('success') }}</div>@endif
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.prototypes.field.title') }}</th>
                <th>{{ __('portal.prototypes.field.category') }}</th>
                <th>{{ __('portal.prototypes.field.status') }}</th>
                <th>{{ __('portal.prototypes.submitted_on') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @forelse($prototypes as $p)
                    <tr>
                        <td><strong>{{ $p->title }}</strong></td>
                        <td>{{ $p->category ?: '—' }}</td>
                        <td><span class="badge bg-{{ $p->getStatusBadgeColor() }}">{{ $p->getStatusLabel() }}</span></td>
                        <td><small class="text-muted">{{ $p->created_at->translatedFormat('M j, Y') }}</small></td>
                        <td class="text-end"><a href="{{ route('prototypes.show', $p) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">{{ __('portal.prototypes.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $prototypes->links() }}</div>
@endsection
