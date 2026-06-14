@extends('layouts.internal-dashboard')
@section('title', __('portal.inventory.upload_excel'))

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header"><h3>{{ __('portal.inventory.upload_excel') }}</h3></div>
            <div class="card-content">
                @include('inventory._messages')

                <p class="text-muted">{{ __('portal.inventory.import_help') }}</p>

                <a href="{{ route('inventory.import.template') }}" class="btn btn-outline-primary mb-3">
                    <i class="fas fa-download"></i> {{ __('portal.inventory.download_template') }}
                </a>

                <form method="POST" action="{{ route('inventory.import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="fas fa-upload"></i> {{ __('portal.inventory.import') }}</button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">{{ __('portal.team.cancel') }}</a>
                    </div>
                </form>

                <hr>
                <h6>{{ __('portal.inventory.columns') }}</h6>
                <p class="text-muted" style="font-size:.85rem;">
                    <code>product_id</code>, <code>name</code>, <code>description</code>,
                    <code>purchase_price</code>, <code>price_student</code>, <code>price_entrepreneur</code>,
                    <code>price_company</code>, <code>unit</code>, <code>category</code>,
                    <code>malas_qty</code>, <code>noura_qty</code>, <code>monshaat_qty</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
