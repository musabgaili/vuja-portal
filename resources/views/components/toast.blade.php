@php
    $messages = collect([
        'success' => session('success'),
        'error' => session('error') ?? $errors->first(),
        'info' => session('info'),
        'warning' => session('warning'),
    ])->filter();
@endphp

@if($messages->isNotEmpty())
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    @foreach($messages as $type => $message)
        @php
            $class = match ($type) {
                'success' => 'text-bg-success',
                'error' => 'text-bg-danger',
                'warning' => 'text-bg-warning',
                default => 'text-bg-info',
            };
        @endphp
        <div class="toast align-items-center {{ $class }} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body">{{ $message }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
            </div>
        </div>
    @endforeach
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast').forEach((toastEl) => {
        new bootstrap.Toast(toastEl).show();
    });
});
</script>
@endif
