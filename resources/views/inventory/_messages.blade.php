@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@foreach($errors->all() as $e)
    <div class="alert alert-danger">{{ $e }}</div>
@endforeach

@if (session('import_failures') && count(session('import_failures')))
    <div class="alert alert-warning">
        <strong>{{ __('portal.inventory.rows_skipped') }}</strong>
        <ul class="mb-0">
            @foreach (session('import_failures') as $f)
                <li>{{ __('portal.inventory.row') }} {{ $f['row'] }} ({{ $f['column'] }}): {{ $f['errors'] }}</li>
            @endforeach
        </ul>
    </div>
@endif
