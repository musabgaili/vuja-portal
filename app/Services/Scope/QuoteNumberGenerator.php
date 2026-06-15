<?php

namespace App\Services\Scope;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Issues the next Q-series quote number from a single locked counter row, so
 * concurrent quote creation can never collide. Format from config('scope.number_format').
 */
class QuoteNumberGenerator
{
    public function next(): string
    {
        $seq = DB::transaction(function () {
            $row = DB::table('scope_counters')->where('key', 'quote')->lockForUpdate()->first();

            if (! $row) {
                DB::table('scope_counters')->insert([
                    'key' => 'quote', 'value' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $current = 0;
            } else {
                $current = (int) $row->value;
            }

            $next = $current + 1;
            DB::table('scope_counters')->where('key', 'quote')->update(['value' => $next, 'updated_at' => now()]);

            return $next;
        });

        return $this->format((string) config('scope.number_format', 'Q{seq4}'), $seq);
    }

    private function format(string $format, int $seq): string
    {
        return strtr($format, [
            '{seq4}' => str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
            '{seq}'  => (string) $seq,
            '{year}' => Carbon::now()->format('Y'),
        ]);
    }
}
