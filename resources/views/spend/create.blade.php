@extends('layouts.internal-dashboard')
@section('title', __('portal.spend.new_request'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('spend.index') }}">{{ __('portal.spend.my_title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.spend.new_request') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-receipt"></i> {{ __('portal.spend.new_request') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.spend.new_sub') }}</p>
</div>

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@include('spend.partials.form', ['action' => route('spend.store')])
@endsection
