<?php

namespace App\Models\Concerns;

use App\Jobs\TranslateModelFields;

/**
 * Gives a model auto-translated, locale-aware text fields.
 *
 * Declare the source columns on the model:
 *     protected array $translatable = ['title', 'description'];
 *
 * For each field "X" the table must have nullable "X_en" and "X_ar" columns.
 * On save, changed source fields are (re)translated: the authored value fills its
 * own-language column and the other language is machine-translated. Read the
 * localized value in views with $model->tr('X') — it always falls back to the
 * authored value when a translation is missing (e.g. no API key).
 */
trait HasAutoTranslations
{
    public static function bootHasAutoTranslations(): void
    {
        static::saved(function ($model) {
            if (! config('translations.enabled', true)) {
                return;
            }

            $changed = collect($model->translatableFields())
                ->filter(fn ($f) => $model->wasRecentlyCreated || $model->wasChanged($f))
                ->values()->all();

            if (! $changed) {
                return;
            }

            $job = new TranslateModelFields(get_class($model), $model->getKey(), $changed);

            // Sync by default so it works with no queue worker; flip to the queue
            // (translations.queue=true) once a worker is running.
            config('translations.queue', false) ? dispatch($job) : dispatch_sync($job);
        });
    }

    /** Source columns that should be auto-translated. */
    public function translatableFields(): array
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    /** Locale-aware value for a translatable field; falls back to the authored value. */
    public function tr(string $field): ?string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $value = $this->getAttribute($field.'_'.$locale);

        return ($value !== null && $value !== '') ? $value : $this->getAttribute($field);
    }
}
