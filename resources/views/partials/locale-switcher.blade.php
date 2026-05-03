@php
    $current = app()->getLocale();
    $locales = [
        'en' => [
            'emoji' => '🇬🇧',
            'label' => __('portal.locale_switcher.english'),
        ],
        'ar' => [
            'emoji' => '🇸🇦',
            'label' => __('portal.locale_switcher.arabic'),
        ],
    ];
@endphp
<div class="dropdown locale-switcher">
    <button type="button"
            class="btn btn-outline-secondary btn-sm dropdown-toggle d-inline-flex align-items-center gap-2 locale-switcher__toggle"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-haspopup="true"
            aria-label="{{ __('portal.locale_switcher.aria_label') }}">
        <span class="locale-switcher__emoji" aria-hidden="true">{{ $locales[$current]['emoji'] }}</span>
        <span class="locale-switcher__label">{{ $locales[$current]['label'] }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1 locale-switcher__menu">
        @foreach ($locales as $code => $meta)
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 rounded-1 @if ($current === $code) active fw-semibold @endif"
                   href="{{ route('locale', ['locale' => $code]) }}"
                   @if ($current === $code) aria-current="true" @endif>
                    <span class="locale-switcher__emoji" aria-hidden="true">{{ $meta['emoji'] }}</span>
                    <span>{{ $meta['label'] }}</span>
                    @if ($current === $code)
                        <i class="fas fa-check ms-auto text-success small" aria-hidden="true"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
