<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('unique_code');
            }
            if (! Schema::hasColumn('transactions', 'payment_proof_uploaded_at')) {
                $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof');
            }
            if (! Schema::hasColumn('transactions', 'proof_notes')) {
                $table->text('proof_notes')->nullable()->after('payment_proof_uploaded_at');
            }
            if (! Schema::hasColumn('transactions', 'proof_archived_at')) {
                $table->timestamp('proof_archived_at')->nullable()->after('proof_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('transactions', 'payment_proof')) {
                $columns[] = 'payment_proof';
            }
            if (Schema::hasColumn('transactions', 'payment_proof_uploaded_at')) {
                $columns[] = 'payment_proof_uploaded_at';
            }
            if (Schema::hasColumn('transactions', 'proof_notes')) {
                $columns[] = 'proof_notes';
            }
            if (Schema::hasColumn('transactions', 'proof_archived_at')) {
                $columns[] = 'proof_archived_at';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
