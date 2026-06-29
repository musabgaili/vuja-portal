@extends('layouts.internal-dashboard')
@section('title', __('portal.notif.title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.notif.title') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-bell"></i> {{ __('portal.notif.title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.notif.page_sub') }}</p>
    </div>
    <a href="{{ route('notifications.preferences') }}" class="btn btn-light"><i class="fas fa-sliders"></i> {{ __('portal.notif.email_settings') }}</a>
</div>

<div class="card">
    <div class="card-content p-0">
        @forelse($items as $it)
            <a href="{{ $it['url'] }}" class="d-flex gap-3 align-items-start p-3 text-decoration-none border-bottom notif-row">
                <span class="notif-ico"><i class="fas {{ $it['icon'] }}"></i></span>
                <span class="flex-grow-1">
                    <span class="d-block" style="color:var(--text-color);">{{ $it['text'] }}</span>
                    <small class="text-muted">{{ $it['ago'] }}</small>
                </span>
                <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} text-muted align-self-center"></i>
            </a>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">{{ __('portal.notif.empty') }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.notif-row:hover{ background:var(--gray-100); }
.notif-ico{ width:36px; height:36px; border-radius:50%; background:rgba(var(--primary-rgb), .12); color:var(--primary-color); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
</style>
@endpush
