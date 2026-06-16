<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Machine translation of short operational text (titles, descriptions) via the
 * Gemini API. Degrades gracefully: with no API key, an empty input, or an API
 * failure it returns null and the caller keeps the original (authored) text.
 */
class TranslationService
{
    /** Translate $text into $targetLocale ('en' | 'ar'). Returns null on no-op/failure. */
    public function translate(?string $text, string $targetLocale): ?string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        $key = config('services.gemini.key');
        if (! $key) {
            return null; // no provider configured — caller falls back to the original
        }

        $target = $targetLocale === 'ar' ? 'Arabic' : 'English';
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        $prompt = "Translate the text below into {$target}. "
            ."Return ONLY the translation — no quotes, labels, notes, or transliteration. "
            ."Keep line breaks, numbers, URLs, e-mail addresses, product names and acronyms unchanged.\n\n"
            .$text;

        try {
            $resp = Http::timeout(20)->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]],
            ])->throw()->json();

            $out = trim((string) data_get($resp, 'candidates.0.content.parts.0.text', ''));

            return $out !== '' ? $out : null;
        } catch (\Throwable $e) {
            Log::warning('TranslationService: translation failed — '.$e->getMessage());

            return null;
        }
    }

    /** Best-effort source-language guess: true when the text contains Arabic letters. */
    public function isArabic(?string $text): bool
    {
        return (bool) preg_match('/\p{Arabic}/u', (string) $text);
    }
}
