@extends('layouts.internal-dashboard')
@section('title', __('portal.chat.mentions_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('chat.index') }}">{{ __('portal.chat.title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.chat.mentions_title') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-at"></i> {{ __('portal.chat.mentions_title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.chat.mentions_subtitle') }}</p>
    </div>
    @if($mentions->where('read_at', null)->isNotEmpty())
    <form method="POST" action="{{ route('chat.mentions.read-all') }}" class="m-0">@csrf
        <button class="btn btn-light"><i class="fas fa-check-double"></i> {{ __('portal.chat.mark_all_read') }}</button>
    </form>
    @endif
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-content p-0">
        <div class="list-group list-group-flush">
        @forelse($mentions as $mention)
            @php $msg = $mention->message; $channel = $msg->channel; @endphp
            <a href="{{ route('chat.show', $channel) }}" class="list-group-item list-group-item-action d-flex gap-3 align-items-start {{ $mention->read_at ? '' : 'bg-light' }}">
                <i class="fas fa-at text-primary mt-1"></i>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="d-flex justify-content-between gap-2">
                        <strong>{{ $msg->author?->name ?? __('portal.chat.unknown_user') }}</strong>
                        <small class="text-muted">{{ $msg->created_at?->diffForHumans() }}</small>
                    </div>
                    <div class="text-muted" style="font-size:.85rem;">
                        {{ $channel->isDm() ? '@ '.$channel->displayName(auth()->user()) : '# '.$channel->displayName(auth()->user()) }}
                    </div>
                    <div style="white-space:normal;">{{ \Illuminate\Support\Str::limit($msg->body, 160) }}</div>
                </div>
                @unless($mention->read_at)<span class="badge bg-danger align-self-center">{{ __('portal.chat.new') }}</span>@endunless
            </a>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-at" style="font-size:2.5rem;opacity:.3;"></i>
                <p class="mt-2 mb-0">{{ __('portal.chat.no_mentions') }}</p>
            </div>
        @endforelse
        </div>
    </div>
</div>
@endsection
