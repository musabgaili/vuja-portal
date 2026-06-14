@extends('layouts.internal-dashboard')
@section('title', __('portal.inventory.edit_item'))

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header"><h3>{{ __('portal.inventory.edit_item') }}</h3></div>
            <div class="card-content">
                @foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach
                <form method="POST" action="{{ route('inventory.update', $item) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @include('inventory._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
