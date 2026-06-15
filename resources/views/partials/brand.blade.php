@php
    $hasLight = file_exists(public_path('images/vd-logo-light.png'));
@endphp
@if($hasLight)
    {{-- Light (colour) logo used in both light and dark mode --}}
    <img src="{{ asset('images/vd-logo-light.png') }}" alt="Vujà Dé" class="brand-logo">
@else
    {{-- Fallback brand mark until the PNG logos are uploaded to public/images/ --}}
    <span class="brand-fallback">
        <svg class="brand-mark" viewBox="0 0 64 64" aria-hidden="true">
            <defs><linearGradient id="bm" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#0F969C"/><stop offset="0.55" stop-color="#0C7075"/><stop offset="1" stop-color="#072E33"/>
            </linearGradient></defs>
            <rect width="64" height="64" rx="14" fill="url(#bm)"/>
            <text x="32" y="43" font-family="Segoe UI, Arial, sans-serif" font-size="30" font-weight="800" fill="#fff" text-anchor="middle" letter-spacing="-1">vd</text>
        </svg>
        <span class="brand-word">Vujà Dé</span>
    </span>
@endif
