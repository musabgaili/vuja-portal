<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('recipient_name', 160)->nullable()->after('client_id');
            $table->string('recipient_email', 255)->nullable()->after('recipient_name');
        });

        // Backfill from linked clients, then allow guest invoices (nullable client_id).
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE invoices
                SET recipient_name = (
                        SELECT name FROM users WHERE users.id = invoices.client_id
                    ),
                    recipient_email = (
                        SELECT lower(email) FROM users WHERE users.id = invoices.client_id
                    )
                WHERE client_id IS NOT NULL
            ');
        } else {
            DB::statement('
                UPDATE invoices
                INNER JOIN users ON users.id = invoices.client_id
                SET invoices.recipient_name = users.name,
                    invoices.recipient_email = LOWER(users.email)
                WHERE invoices.client_id IS NOT NULL
            ');
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
            $table->index('recipient_email');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['recipient_email']);
            $table->dropForeign(['client_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropColumn(['recipient_name', 'recipient_email']);
        });
    }
};
