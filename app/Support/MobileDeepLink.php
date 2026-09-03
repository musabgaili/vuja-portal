<?php

namespace App\Support;

/**
 * Map portal web URLs to Flutter app deep-link paths (without the scheme).
 * Universal links use https://host/app/{path}; the app scheme is vujade://app/{path}.
 */
final class MobileDeepLink
{
    public static function fromUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $path = '/'.ltrim($path, '/');

        if (preg_match('#/internal/projects/show/(\d+)#', $path, $m)) {
            return 'projects/'.$m[1];
        }
        if (preg_match('#/internal/chat/(\d+)#', $path, $m)) {
            return 'chat/'.$m[1];
        }
        if (preg_match('#/internal/staff-tasks#', $path)) {
            return 'tasks/staff';
        }
        if (preg_match('#/internal/approvals#', $path)) {
            return 'approvals';
        }
        if (preg_match('#/internal/meetings#', $path, $m)) {
            return 'meetings';
        }
        if (preg_match('#/meetings#', $path)) {
            return 'meetings';
        }
        if (preg_match('#/internal/engagement#', $path)) {
            return 'engagement';
        }
        if (preg_match('#/internal/weekly-planner#', $path)) {
            return 'weekly-plan';
        }

        $trimmed = ltrim($path, '/');

        return $trimmed !== '' ? $trimmed : null;
    }

    public static function absolute(string $path): string
    {
        $scheme = config('mobile.scheme', 'vujade');

        return $scheme.'://app/'.ltrim($path, '/');
    }
}
