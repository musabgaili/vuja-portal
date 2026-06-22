<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Report (and, only when explicitly confirmed, remove) seeded/demo/test ACCOUNTS
 * and their data. Real client/team accounts are never auto-removed:
 *   - Default / --dry-run: prints a candidate report + each account's data footprint.
 *   - --users=1,2,3 --force: deletes ONLY those ids, inside a transaction. The DB's
 *     own foreign keys handle the cascade (nullOnDelete / cascadeOnDelete); if a
 *     restrict FK blocks a delete the transaction rolls back and names the table.
 *   - Accounts on the real domain are refused unless --allow-real is also passed.
 */
class CleanDemoData extends Command
{
    protected $signature = 'portal:clean-demo
        {--users= : Comma-separated user ids to remove (from the report). Required to delete anything.}
        {--demo-domain=vujade.com : Email domain treated as demo/seeded (the real domain is vujadesa.com).}
        {--force : Actually delete. Without it, the command only reports.}
        {--allow-real : Allow deleting an account NOT on the demo domain (extra confirmation).}';

    protected $description = 'Report and (with explicit ids + --force) remove seeded/demo/test accounts and their data.';

    /** Names the original seeders created — flagged even if the email pattern changes. */
    private const SEEDED_NAMES = [
        'John Client', 'Sarah Employee', 'Mike Manager', 'Lisa Project Manager',
        'Alex Rivera (Client)', 'Sam Chen (Client)', 'Jordan Internal',
    ];

    /** Tables to summarise per account so the operator sees the blast radius. */
    private const FOOTPRINT = [
        ['projects', 'client_id'], ['quotes', 'created_by'], ['weekly_plans', 'user_id'],
        ['opportunities', 'assigned_to'], ['staff_tasks', 'assigned_to'], ['project_people', 'user_id'],
        ['chat_messages', 'user_id'], ['chat_channel_user', 'user_id'],
        ['engagement_logs', 'user_id'], ['service_requests', 'user_id'], ['spend_requests', 'requester_id'],
    ];

    public function handle(): int
    {
        $demoDomain = trim((string) $this->option('demo-domain'));
        $explicit = $this->parseIds((string) $this->option('users'));

        $candidates = $this->candidates($demoDomain);

        if (empty($this->option('users'))) {
            $this->report($candidates, $demoDomain);

            return self::SUCCESS;
        }

        // --- Targeted removal of explicitly confirmed ids ---
        $targets = User::whereIn('id', $explicit)->get();
        $missing = array_diff($explicit, $targets->pluck('id')->all());
        if ($missing) {
            $this->error('No such user id(s): '.implode(', ', $missing));

            return self::FAILURE;
        }

        // Guard real accounts unless explicitly allowed.
        $real = $targets->reject(fn ($u) => $this->looksDemo($u, $demoDomain));
        if ($real->isNotEmpty() && ! $this->option('allow-real')) {
            $this->error('These ids are NOT on the demo domain and look real — refusing without --allow-real:');
            foreach ($real as $u) {
                $this->line("  #{$u->id}  {$u->name} <{$u->email}>");
            }

            return self::FAILURE;
        }

        $this->table(['ID', 'Name', 'Email', 'Type', 'Footprint'],
            $targets->map(fn ($u) => [$u->id, $u->name, $u->email, $u->type, $this->footprintSummary($u->id)])->all());

        if (! $this->option('force')) {
            $this->warn('DRY RUN — nothing deleted. Re-run with --force to remove the '.$targets->count().' account(s) above.');

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($targets) {
                foreach ($targets as $u) {
                    $u->delete();   // DB foreign keys cascade / null per their definitions
                }
            });
        } catch (QueryException $e) {
            $this->error('Delete rolled back — a foreign key blocked it (no changes were made):');
            $this->line('  '.$e->getMessage());
            $this->line('Resolve/reassign the referencing rows, then re-run.');

            return self::FAILURE;
        }

        $this->info('Removed '.$targets->count().' account(s) and their cascaded data.');

        return self::SUCCESS;
    }

    private function report($candidates, string $demoDomain): void
    {
        $this->info("Suspected demo/seed/test accounts (demo domain: @{$demoDomain}). NOTHING is deleted here.");
        if ($candidates->isEmpty()) {
            $this->line('  (none found)');
        } else {
            $this->table(['ID', 'Name', 'Email', 'Role', 'Type', 'Why flagged', 'Footprint'],
                $candidates->map(fn ($u) => [
                    $u->id, $u->name, $u->email, optional($u->role)->value ?? $u->role, $u->type,
                    $this->reason($u, $demoDomain), $this->footprintSummary($u->id),
                ])->all());
        }

        $kept = User::count() - $candidates->count();
        $this->newLine();
        $this->info("Real/other accounts that would be KEPT: {$kept}");
        $this->newLine();
        $this->comment('To remove specific ones, copy their ids from the report:');
        $this->comment('  php artisan portal:clean-demo --users=ID,ID            # dry-run preview of those ids');
        $this->comment('  php artisan portal:clean-demo --users=ID,ID --force    # actually remove them');
    }

    /** @return \Illuminate\Support\Collection<int,User> */
    private function candidates(string $demoDomain)
    {
        return User::query()
            ->where(function ($q) use ($demoDomain) {
                $q->where('email', 'like', '%@'.$demoDomain)
                    ->orWhere('email', 'like', '%test%')
                    ->orWhere('email', 'like', '%demo%')
                    ->orWhere('email', 'like', '%@example.%')
                    ->orWhereIn('name', self::SEEDED_NAMES);
            })
            ->orderBy('id')
            ->get();
    }

    private function looksDemo(User $u, string $demoDomain): bool
    {
        return str_ends_with((string) $u->email, '@'.$demoDomain)
            || str_contains((string) $u->email, 'test')
            || str_contains((string) $u->email, 'demo')
            || str_contains((string) $u->email, '@example.')
            || in_array($u->name, self::SEEDED_NAMES, true);
    }

    private function reason(User $u, string $demoDomain): string
    {
        $r = [];
        if (str_ends_with((string) $u->email, '@'.$demoDomain)) {
            $r[] = 'demo domain';
        }
        if (in_array($u->name, self::SEEDED_NAMES, true)) {
            $r[] = 'seeded name';
        }
        if (str_contains((string) $u->email, 'test') || str_contains((string) $u->email, 'demo') || str_contains((string) $u->email, '@example.')) {
            $r[] = 'test/demo email';
        }

        return implode(', ', $r) ?: '—';
    }

    private function footprintSummary(int $userId): string
    {
        $parts = [];
        foreach (self::FOOTPRINT as [$table, $col]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $col)) {
                continue;
            }
            $n = DB::table($table)->where($col, $userId)->count();
            if ($n > 0) {
                $parts[] = "{$table}:{$n}";
            }
        }

        return $parts ? implode(' ', $parts) : 'no records';
    }

    /** @return array<int,int> */
    private function parseIds(string $csv): array
    {
        return collect(explode(',', $csv))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->unique()->values()->all();
    }
}
