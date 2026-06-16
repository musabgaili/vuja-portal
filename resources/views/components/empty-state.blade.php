@props(['icon' => 'fa-inbox', 'title' => '', 'text' => ''])
{{-- Reusable empty-state: icon + title + helper text + optional CTA (slot). --}}
<div class="empty-state">
    <div class="empty-state__icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></div>
    @if($title)<div class="empty-state__title">{{ $title }}</div>@endif
    @if($text)<div class="empty-state__text">{{ $text }}</div>@endif
    {{ $slot }}
</div>
