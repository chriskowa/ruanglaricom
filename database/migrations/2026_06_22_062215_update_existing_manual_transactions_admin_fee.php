<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations .
     */
    public function up(): void
    {
        // Update 'manual' transactions
        $manualTx = DB::table('transactions')
            ->join('events', 'transactions.event_id', '=', 'events.id')
            ->where('transactions.payment_gateway', 'manual')
            ->where('transactions.admin_fee', 0)
            ->where('events.platform_fee', '>', 0)
            ->select('transactions.id', 'events.platform_fee')
            ->get();

        foreach ($manualTx as $tx) {
            DB::table('transactions')
                ->where('id', $tx->id)
                ->update(['admin_fee' => $tx->platform_fee]);
        }

        // Update 'manual_csv' transactions
        $csvTx = DB::table('transactions')
            ->join('events', 'transactions.event_id', '=', 'events.id')
            ->where('transactions.payment_gateway', 'manual_csv')
            ->where('transactions.admin_fee', 0)
            ->where('events.platform_fee', '>', 0)
            ->select('transactions.id', 'events.platform_fee')
            ->get();

        foreach ($csvTx as $tx) {
            $count = DB::table('participants')
                ->where('transaction_id', $tx->id)
                ->count();
            if ($count > 0) {
                DB::table('transactions')
                    ->where('id', $tx->id)
                    ->update(['admin_fee' => $tx->platform_fee * $count]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or we can set it back to 0 if needed, but not necessary to undo data correction.
    }
};
