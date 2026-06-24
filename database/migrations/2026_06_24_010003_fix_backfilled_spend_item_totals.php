<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrects any single-line backfilled request whose item total drifted from the
 * authoritative header amount (an earlier backfill rounded unit = amount/quantity).
 * Only touches requests that have exactly one item and whose line total no longer
 * equals the header — i.e. the auto-backfilled rows — collapsing them to qty 1 at
 * unit = amount so itemsTotal() == amount exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('spend_requests')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $items = DB::table('spend_request_items')->where('spend_request_id', $r->id)->get();
                if ($items->count() !== 1) {
                    continue; // multi-item (user-authored) requests are already exact
                }
                $item = $items->first();
                $lineTotal = round((int) $item->quantity * (float) $item->unit_amount, 2);
                if ($lineTotal !== round((float) ($r->amount ?? 0), 2)) {
                    DB::table('spend_request_items')->where('id', $item->id)->update([
                        'quantity' => 1,
                        'unit_amount' => (float) ($r->amount ?? 0),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Corrective only.
    }
};
