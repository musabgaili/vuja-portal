{{-- Light/dark theme toggle. Icon is set on load by partials/theme-head. --}}
<button type="button" class="theme-toggle-btn" onclick="toggleTheme()"
        aria-label="{{ __('portal.app.toggle_theme') ?? 'Toggle theme' }}"
        title="{{ __('portal.app.toggle_theme') ?? 'Toggle theme' }}">
    <i data-theme-icon class="fas fa-moon"></i>
</button>
