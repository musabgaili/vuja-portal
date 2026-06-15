<?php

namespace Database\Seeders;

use App\Models\ScopeBoilerplate;
use Illuminate\Database\Seeder;

/**
 * Seeds the editable bilingual boilerplate used on scope/quotation documents.
 * Defaults mirror lang/{en,ar}/scope.php; managers can edit the DB rows later.
 */
class ScopeBoilerplateSeeder extends Seeder
{
    public function run(): void
    {
        $days = (int) config('scope.validity_days', 30);
        $keys = ['pricing_intro', 'terms_change', 'terms_confidentiality', 'terms_validity'];

        foreach (['en', 'ar'] as $lang) {
            foreach ($keys as $key) {
                ScopeBoilerplate::updateOrCreate(
                    ['tier' => 'all', 'language' => $lang, 'section_key' => $key],
                    ['content' => trans('scope.'.$key, ['days' => $days], $lang)],
                );
            }
        }
    }
}
