@extends('layouts.internal-dashboard')
@section('title', __('portal.notif_prefs.title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.notif_prefs.title') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-envelope-open-text"></i> {{ __('portal.notif_prefs.title') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.notif_prefs.subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="alert alert-info"><i class="fas fa-circle-info"></i> {{ __('portal.notif_prefs.note') }}</div>

<form method="POST" action="{{ route('notifications.preferences.update') }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-header"><span class="card-title">{{ __('portal.notif_prefs.email_section') }}</span></div>
        <div class="card-content p-0">
            <table class="table mb-0 align-middle">
                <thead><tr>
                    <th>{{ __('portal.notif_prefs.col_event') }}</th>
                    <th class="text-end" style="width:120px">{{ __('portal.notif_prefs.col_email') }}</th>
                </tr></thead>
                <tbody>
                @foreach($types as $key => $cfg)
                    <tr>
                        <td>{{ __($cfg['label']) }}</td>
                        <td class="text-end">
                            <div class="form-check form-switch d-inline-block">
                                <input type="checkbox" class="form-check-input" role="switch"
                                       id="pref_{{ $key }}" name="prefs[{{ $key }}]" value="1"
                                       @checked($user->wantsEmail($key))>
                                <label class="form-check-label visually-hidden" for="pref_{{ $key }}">{{ __($cfg['label']) }}</label>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if(empty($types))
                    <tr><td colspan="2" class="text-muted text-center py-3">{{ __('portal.notif_prefs.none') }}</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.notif_prefs.save') }}</button>
    </div>
</form>
@endsection
