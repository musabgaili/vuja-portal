<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Diagnoses the Gemini API key/model the AI Scope Planner uses. Pings
 * generateContent and lists the models the key can actually call, so a 404 /
 * "Offline draft" can be traced to the real cause (wrong model, retired model,
 * bad key, stale config cache). The API key value is NEVER printed.
 */
class GeminiCheckCommand extends Command
{
    protected $signature = 'scope:gemini-check {--prompt=Say hello in three words.}';

    protected $description = 'Diagnose the Gemini API key/model used by the AI Scope Planner (key value is never shown).';

    public function handle(): int
    {
        $key = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-1.5-flash');

        $this->line('Configured model : '.$model);
        $this->line('API key          : '.($key !== '' ? 'set ('.strlen($key).' chars)' : 'NOT SET'));
        $this->line('Config cached    : '.(file_exists(base_path('bootstrap/cache/config.php')) ? 'yes (run config:cache after .env edits)' : 'no (reads .env live)'));

        if ($key === '') {
            $this->error('No GEMINI_API_KEY configured. Add it to .env, then: php artisan config:clear && php artisan config:cache');

            return self::FAILURE;
        }

        // 1) Ping generateContent on the configured model.
        $this->newLine();
        $this->info('1) Testing generateContent on "'.$model.'" ...');
        try {
            $resp = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
                ['contents' => [['parts' => [['text' => (string) $this->option('prompt')]]]]],
            );
            $this->line('   HTTP '.$resp->status());
            if ($resp->successful()) {
                $text = data_get($resp->json(), 'candidates.0.content.parts.0.text', '');
                $this->info('   OK - reply: '.trim((string) $text));
            } else {
                $this->error('   FAILED - response body:');
                $this->line('   '.Str::limit($resp->body(), 700));
            }
        } catch (\Throwable $e) {
            $this->error('   EXCEPTION: '.$e->getMessage());
        }

        // 2) List models this key can call with generateContent.
        $this->newLine();
        $this->info('2) Models available to this key (support generateContent):');
        try {
            $list = Http::timeout(30)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}&pageSize=200");
            if ($list->successful()) {
                $models = collect(data_get($list->json(), 'models', []))
                    ->filter(fn ($m) => in_array('generateContent', (array) data_get($m, 'supportedGenerationMethods', []), true))
                    ->map(fn ($m) => str_replace('models/', '', (string) data_get($m, 'name', '')))
                    ->sort()->values();

                if ($models->isEmpty()) {
                    $this->warn('   (none returned)');
                } else {
                    foreach ($models as $m) {
                        $this->line('   - '.$m.($m === $model ? '   <== configured' : ''));
                    }
                    if (! $models->contains($model)) {
                        $this->newLine();
                        $this->error('Your configured model "'.$model.'" is NOT in the list above — that is the 404.');
                        $this->line('Set GEMINI_MODEL to one of the listed ids (e.g. gemini-2.5-flash), then: php artisan config:clear && php artisan config:cache');
                    }
                }
            } else {
                $this->error('   list failed: HTTP '.$list->status());
                $this->line('   '.Str::limit($list->body(), 400));
            }
        } catch (\Throwable $e) {
            $this->error('   EXCEPTION: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
