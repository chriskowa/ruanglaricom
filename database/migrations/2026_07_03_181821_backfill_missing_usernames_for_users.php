<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::whereNull('username')
            ->orWhere('username', '')
            ->get()
            ->each(function ($user) {
                $slug = Str::slug($user->name);
                $count = 1;
                while (User::where('username', $slug)->exists()) {
                    $slug = Str::slug($user->name) . $count++;
                }
                $user->username = $slug;
                $user->save();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback action needed for database values backfill
    }
};
