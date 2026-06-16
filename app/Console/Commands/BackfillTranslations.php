<?php

namespace App\Console\Commands;

use App\Jobs\TranslateModelFields;
use Illuminate\Console\Command;

/**
 * Fills en/ar columns for existing rows of every auto-translatable model.
 * Runs the translation synchronously per row (uses Gemini when GEMINI_API_KEY is
 * set; otherwise just records the authored value in its own-language column).
 *
 *   php artisan translations:backfill
 *   php artisan translations:backfill --model=ProjectMilestone
 */
class BackfillTranslations extends Command
{
    protected $signature = 'translations:backfill {--model= : Only this model (class basename)}';

    protected $description = 'Backfill bilingual (en/ar) columns for existing operational content.';

    private array $models = [
        \App\Models\Project::class,
        \App\Models\ProjectMilestone::class,
        \App\Models\ProjectTask::class,
        \App\Models\IdeaRequest::class,
        \App\Models\ConsultationRequest::class,
        \App\Models\ResearchRequest::class,
        \App\Models\IpRegistration::class,
        \App\Models\CopyrightRegistration::class,
        \App\Models\PrototypeRequest::class,
    ];

    public function handle(): int
    {
        if (! config('services.gemini.key')) {
            $this->warn('No GEMINI_API_KEY set — source columns will be filled but no translation will be produced.');
        }

        $only = $this->option('model');

        foreach ($this->models as $class) {
            if ($only && class_basename($class) !== $only) {
                continue;
            }

            $fields = (new $class)->translatableFields();
            if (! $fields) {
                continue;
            }

            $processed = 0;
            $class::query()->chunkById(100, function ($rows) use ($fields, &$processed) {
                foreach ($rows as $row) {
                    $needs = collect($fields)->contains(function ($f) use ($row) {
                        if (trim((string) $row->getAttribute($f)) === '') {
                            return false;
                        }

                        return blank($row->getAttribute($f.'_en')) || blank($row->getAttribute($f.'_ar'));
                    });

                    if ($needs) {
                        dispatch_sync(new TranslateModelFields(get_class($row), $row->getKey(), $fields));
                        $processed++;
                    }
                }
            });

            $this->info(class_basename($class).": processed {$processed} row(s).");
        }

        return self::SUCCESS;
    }
}
