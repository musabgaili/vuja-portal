@extends('layouts.dashboard')

@section('title', __('portal.threed.my_title'))
@section('page-title', __('portal.threed.my_title'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cubes"></i> {{ __('portal.threed.my_title') }}</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('threed.create', ['type' => 'printing']) }}" class="btn btn-primary btn-sm"><i class="fas fa-print"></i> {{ __('portal.threed.new_printing') }}</a>
            <a href="{{ route('threed.create', ['type' => 'design']) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-pen-ruler"></i> {{ __('portal.threed.new_design') }}</a>
        </div>
    </div>
    <div class="card-content p-0">
        @if(session('success'))<div class="alert alert-success m-3">{{ session('success') }}</div>@endif
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.threed.field.title') }}</th>
                <th>{{ __('portal.threed.field.type') }}</th>
                <th>{{ __('portal.threed.field.status') }}</th>
                <th>{{ __('portal.threed.submitted_on') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td><strong>{{ $r->tr('title') }}</strong></td>
                        <td><span class="badge bg-{{ $r->isPrinting() ? 'info' : 'secondary' }}"><i class="fas fa-{{ $r->isPrinting() ? 'print' : 'pen-ruler' }}"></i> {{ $r->typeLabel() }}</span></td>
                        <td><span class="badge bg-{{ $r->getStatusBadgeColor() }}">{{ $r->getStatusLabel() }}</span></td>
                        <td><small class="text-muted">{{ $r->created_at->translatedFormat('M j, Y') }}</small></td>
                        <td class="text-end"><a href="{{ route('threed.show', $r) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">{{ __('portal.threed.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
