@extends('layouts.internal-dashboard')
@section('title', __('portal.chat.browse_title'))

@section('content')
<div class="page-hero"><h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-compass"></i> {{ __('portal.chat.browse_title') }}</h1></div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<a href="{{ route('chat.index') }}" class="btn btn-light mb-3"><i class="fas fa-arrow-left"></i> {{ __('portal.chat.back_to_chat') }}</a>

<div class="card"><div class="card-content">
  <p class="text-muted" style="font-size:.9rem;">{{ __('portal.chat.browse_intro') }}</p>
  @forelse($channels as $c)
  <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
    <div>
      <strong># {{ $c->name }}</strong>
      @if($c->is_private)<span class="badge bg-secondary">{{ __('portal.chat.private') }}</span>@endif
      <span class="text-muted" style="font-size:.85rem;">· {{ $c->members_count }} {{ __('portal.chat.members') }}</span>
      @if($c->description)<div class="text-muted" style="font-size:.85rem;">{{ $c->description }}</div>@endif
    </div>
    <div>
      @can('view', $c)
        <a href="{{ route('chat.show', $c) }}" class="btn btn-sm btn-primary"><i class="fas fa-arrow-right"></i> {{ __('portal.chat.open') }}</a>
      @elseif(in_array($c->id, $pending))
        <button class="btn btn-sm btn-light" disabled><i class="fas fa-clock"></i> {{ __('portal.chat.requested') }}</button>
      @else
        <form method="POST" action="{{ route('chat.join.request', $c) }}" class="m-0">
          @csrf
          <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-user-plus"></i> {{ __('portal.chat.request_to_join') }}</button>
        </form>
      @endcan
    </div>
  </div>
  @empty
  <p class="text-muted mb-0">{{ __('portal.chat.no_discoverable') }}</p>
  @endforelse
</div></div>
@endsection
