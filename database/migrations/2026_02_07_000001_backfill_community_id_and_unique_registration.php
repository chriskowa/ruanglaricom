<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_registrations') || ! Schema::hasTable('communities')) {
            return;
        }

        if (! Schema::hasColumn('community_registrations', 'community_id')) {
            return;
        }

        DB::table('community_registrations')
            ->whereNull('community_id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $communityName = trim((string) ($row->community_name ?? ''));
                    $picName = trim((string) ($row->pic_name ?? ''));
                    $picEmail = strtolower(trim((string) ($row->pic_email ?? '')));
                    $picPhone = trim((string) ($row->pic_phone ?? ''));

                    if ($communityName === '' && $picEmail === '') {
                        continue;
                    }

                    $existing = DB::table('communities')
                        ->when($communityName !== '', function ($q) use ($communityName) {
                            $q->whereRaw('LOWER(name) = ?', [strtolower($communityName)]);
                        })
                        ->when($picEmail !== '', function ($q) use ($picEmail) {
                            $q->orWhere('pic_email', $picEmail);
                        })
                        ->first();

                    $communityId = null;
                    if ($existing) {
                        $communityId = $existing->id;
                    } else {
                        $baseSlug = Str::slug($communityName !== '' ? $communityName : $picEmail);
                        $slug = $baseSlug !== '' ? $baseSlug : Str::random(10);
                        $base = $baseSlug !== '' ? $baseSlug : $slug;
                        $counter = 2;
                        while (DB::table('communities')->where('slug', $slug)->exists()) {
                            $slug = $base.'-'.$counter;
                            $counter++;
                        }

                        $now = now();
                        $communityId = DB::table('communities')->insertGetId([
                            'name' => $communityName !== '' ? $communityName : $slug,
                            'slug' => $slug,
                            'pic_name' => $picName !== '' ? $picName : '-',
                            'pic_email' => $picEmail !== '' ? $picEmail : ($slug.'@invalid.local'),
                            'pic_phone' => $picPhone !== '' ? $picPhone : '-',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    DB::table('community_registrations')
                        ->where('id', $row->id)
                        ->update([
                            'community_id' => $communityId,
                            'updated_at' => now(),
                        ]);
                }
            }, 'id');

        $dupes = DB::table('community_registrations')
            ->select('event_id', 'community_id', DB::raw('COUNT(*) as c'))
            ->whereNotNull('community_id')
            ->groupBy('event_id', 'community_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($dupes as $g) {
            $ids = DB::table('community_registrations')
                ->where('event_id', $g->event_id)
                ->where('community_id', $g->community_id)
                ->orderByDesc('id')
                ->pluck('id')
                ->all();

            if (count($ids) < 2) {
                continue;
            }

            $keepId = array_shift($ids);
            $dropIds = $ids;

            if (Schema::hasTable('community_participants')) {
                DB::table('community_participants')
                    ->whereIn('community_registration_id', $dropIds)
                    ->update(['community_registration_id' => $keepId]);
            }

            if (Schema::hasTable('community_invoices')) {
                DB::table('community_invoices')
                    ->whereIn('community_registration_id', $dropIds)
                    ->update(['community_registration_id' => $keepId]);
            }

            DB::table('community_registrations')->whereIn('id', $dropIds)->delete();
        }

        Schema::table('community_registrations', function (Blueprint $table) {
            $table->unique(['event_id', 'community_id'], 'community_registrations_event_community_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('community_registrations')) {
            return;
        }

        Schema::table('community_registrations', function (Blueprint $table) {
            $table->dropUnique('community_registrations_event_community_unique');
        });
    }
};
