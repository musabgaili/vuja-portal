@extends('layouts.internal-dashboard')
@section('title', __('portal.spend.edit_request'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('spend.index') }}">{{ __('portal.spend.my_title') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('spend.show', $spend) }}">{{ $spend->title }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.spend.edit_request') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-pen"></i> {{ __('portal.spend.edit_request') }}</h1>
</div>

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@include('spend.partials.form', ['action' => route('spend.update', $spend)])
@endsection
