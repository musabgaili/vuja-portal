<?php

namespace App\Jobs;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Fills the per-language columns (X_en / X_ar) for a model's translatable fields.
 * The authored value lands in its own-language column; the other language is
 * machine-translated. Writes with saveQuietly() so it does not re-trigger the
 * HasAutoTranslations saved() hook.
 */
class TranslateModelFields implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @param  class-string  $modelClass */
    public function __construct(
        public string $modelClass,
        public int|string $modelId,
        public array $fields,
    ) {}

    public function handle(TranslationService $translator): void
    {
        $model = $this->modelClass::find($this->modelId);
        if (! $model) {
            return;
        }

        $updates = [];

        foreach ($this->fields as $field) {
            $raw = trim((string) $model->getAttribute($field));
            if ($raw === '') {
                continue;
            }

            $srcLocale = $translator->isArabic($raw) ? 'ar' : 'en';
            $otherLocale = $srcLocale === 'ar' ? 'en' : 'ar';

            // Authored text is canonical for its own language.
            $updates[$field.'_'.$srcLocale] = $raw;

            // Translate to the other language; keep any existing value on failure.
            $translated = $translator->translate($raw, $otherLocale);
            if ($translated !== null) {
                $updates[$field.'_'.$otherLocale] = $translated;
            }
        }

        if ($updates) {
            $model->forceFill($updates)->saveQuietly();
        }
    }
}
