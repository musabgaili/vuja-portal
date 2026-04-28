@php
    $current = app()->getLocale();
@endphp
<div class="locale-switcher btn-group btn-group-sm" role="group" aria-label="Language">
    <a href="{{ route('locale', ['locale' => 'en']) }}"
       class="btn btn-outline-secondary @if($current === 'en') active @endif"
       @if($current === 'en') aria-current="true" @endif>{{ __('portal.language_en') }}</a>
    <a href="{{ route('locale', ['locale' => 'ar']) }}"
       class="btn btn-outline-secondary @if($current === 'ar') active @endif"
       @if($current === 'ar') aria-current="true" @endif>{{ __('portal.language_ar') }}</a>
</div>
