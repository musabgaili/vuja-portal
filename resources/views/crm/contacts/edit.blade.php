@extends('layouts.internal-dashboard')
@section('title', __('portal.crm.edit'))

@section('content')
<div class="page-hero"><h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-pen"></i> {{ $contact->name }}</h1></div>
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach
<div class="card"><div class="card-content">
    <form method="POST" action="{{ route('contacts.update', $contact) }}">
        @csrf @method('PUT')
        @include('crm.contacts._form')
        <div class="d-flex gap-2 mt-3 align-items-center">
            <button class="btn btn-primary"><i class="fas fa-save"></i> {{ __('portal.crm.save') }}</button>
            <a href="{{ route('contacts.index') }}" class="btn btn-secondary">{{ __('portal.crm.cancel') }}</a>
        </div>
    </form>
    <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete?')" class="mt-2">
        @csrf @method('DELETE')
        <button class="btn btn-outline-primary"><i class="fas fa-trash"></i> {{ __('portal.crm.delete') }}</button>
    </form>
</div></div>
@endsection
