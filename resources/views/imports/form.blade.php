@extends('layouts.internal-dashboard')
@section('title', $title)

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-file-excel"></i> {{ $title }}</h3></div>
            <div class="card-content">
                @include('inventory._messages')

                <p class="text-muted">{{ __('portal.import.help') }}</p>

                <a href="{{ $templateRoute }}" class="btn btn-outline-primary mb-3">
                    <i class="fas fa-download"></i> {{ __('portal.import.download_template') }}
                </a>

                <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="fas fa-upload"></i> {{ __('portal.import.import') }}</button>
                        <a href="{{ $backRoute }}" class="btn btn-secondary">{{ __('portal.team.cancel') }}</a>
                    </div>
                </form>

                <hr>
                <h6>{{ __('portal.import.columns') }}</h6>
                <p class="text-muted" style="font-size:.85rem;">
                    @foreach($columns as $c)<code>{{ $c }}</code> @endforeach
                </p>
                @isset($note)<p class="text-muted" style="font-size:.8rem;">{{ $note }}</p>@endisset
            </div>
        </div>
    </div>
</div>
@endsection
