@extends('layouts.internal-dashboard')
@section('title', __('portal.crm.new_contact'))

@section('content')
<div class="page-hero"><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-address-book"></i> {{ __('portal.crm.new_contact') }}</h1></div>
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach
<div class="card"><div class="card-content">
    <form method="POST" action="{{ route('contacts.store') }}">
        @csrf
        @include('crm.contacts._form')
        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.crm.save') }}</button>
            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">{{ __('portal.crm.cancel') }}</a>
        </div>
    </form>
</div></div>
@endsection
