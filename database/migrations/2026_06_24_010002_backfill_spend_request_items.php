<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill every existing single-line spend request into the new line-item shape
 * (one item per request) and move its legacy receipt columns into the files
 * table. The request's `amount` header is left untouched — it stays the
 * authoritative total used by the budget/ledger logic — so this is non-destructive.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('spend_requests')->orderBy('id')->chunkById(200, function ($rows) use ($now) {
            foreach ($rows as $r) {
                // Skip if this request already has line items (idempotent re-run).
                $hasItems = DB::table('spend_request_items')->where('spend_request_id', $r->id)->exists();
                if (! $hasItems) {
                    $qty = max((int) ($r->quantity ?? 1), 1);
                    $amount = (float) ($r->amount ?? 0);
                    $unit = $qty > 0 ? round($amount / $qty, 2) : $amount;

                    DB::table('spend_request_items')->insert([
                        'spend_request_id' => $r->id,
                        'description' => $r->title,
                        'quantity' => $qty,
                        'unit_amount' => $unit,
                        'product_url' => $r->product_url,
                        'sort_order' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                foreach ([['receipt_file', 'receipt'], ['actual_receipt_file', 'actual_receipt']] as [$col, $kind]) {
                    if (! empty($r->$col)) {
                        $exists = DB::table('spend_request_files')
                            ->where('spend_request_id', $r->id)->where('path', $r->$col)->exists();
                        if (! $exists) {
                            DB::table('spend_request_files')->insert([
                                'spend_request_id' => $r->id,
                                'kind' => $kind,
                                'path' => $r->$col,
                                'original_name' => null,
                                'uploaded_by' => $kind === 'actual_receipt' ? ($r->reviewed_by ?? $r->requester_id) : $r->requester_id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Backfill only — items/files tables are dropped by their own migrations.
    }
};
