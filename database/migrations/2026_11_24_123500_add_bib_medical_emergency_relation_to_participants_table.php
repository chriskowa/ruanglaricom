<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (! Schema::hasColumn('participants', 'bib_name')) {
                $table->string('bib_name', 30)->nullable()->after('name');
            }
            if (! Schema::hasColumn('participants', 'emergency_contact_relation')) {
                $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact_name');
            }
            if (! Schema::hasColumn('participants', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('blood_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $cols = [];
            foreach (['bib_name', 'emergency_contact_relation', 'medical_history'] as $col) {
                if (Schema::hasColumn('participants', $col)) {
                    $cols[] = $col;
                }
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
