@extends('layouts.internal-dashboard')
@section('title', __('portal.team.members_title'))
@section('page-title', __('portal.team.members_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.team.members_title') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ __('portal.team.members_title') }}</h3>
        <a href="{{ route('team.invite') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus"></i> {{ __('portal.team.invite_member') }}
        </a>
    </div>
    <div class="card-content">
        @if($teamMembers->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('portal.team.col_name') }}</th>
                    <th>{{ __('portal.team.col_email') }}</th>
                    <th>{{ __('portal.team.col_phone') }}</th>
                    <th>{{ __('portal.team.col_role') }}</th>
                    <th>{{ __('portal.team.col_status') }}</th>
                    <th>{{ __('portal.team.col_joined') }}</th>
                    <th>{{ __('portal.team.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teamMembers as $member)
                <tr>
                    <td><strong>{{ $member->name }}</strong></td>
                    <td>
                        <i class="fas fa-envelope"></i> {{ $member->email }}
                    </td>
                    <td>
                        @if($member->phone)
                        <i class="fas fa-phone"></i> {{ $member->phone }}
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($member->roles->count() > 0)
                        <span class="badge bg-primary">{{ $member->roles->first()->name }}</span>
                        @else
                        <span class="badge bg-secondary">{{ $member->role }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $member->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($member->status->value) }}
                        </span>
                    </td>
                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($member->id !== auth()->id())
                        <form method="POST" action="{{ route('team.destroy', $member) }}" style="display: inline;" onsubmit="return confirm('{{ __('portal.team.confirm_remove_member') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $teamMembers->links('pagination::bootstrap-5') }}
        @else
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4>{{ __('portal.team.empty_title') }}</h4>
            <p>{{ __('portal.team.empty_body') }}</p>
            <a href="{{ route('team.invite') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> {{ __('portal.team.invite_team_member') }}
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

