<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * transactions.method was an ENUM whose allowed values changed every time
 * the payment gateway changed (NOWPayment -> visa -> stripe). Editing the
 * original migration never reached the live database, so a Stripe payment
 * could not be recorded there. A plain string column accepts any gateway
 * key from config/payments.php and keeps historic rows (e.g. 'NOWPayment').
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY method VARCHAR(30) NOT NULL");
        }
    }

    public function down(): void
    {
        // Intentionally left as a string: narrowing back to an ENUM would
        // fail on rows written by other gateways.
    }
};
