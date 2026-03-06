<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cari event dengan hardcoded 'latbarkamis'
        $event = Event::where('hardcoded', 'latbarkamis')->first();
        
        if ($event) {
            // Ambil config yang ada atau inisialisasi array baru
            $config = $event->whatsapp_config ?? [];
            
            // Set enabled ke false untuk menonaktifkan notifikasi WA
            $config['enabled'] = false;
            
            // Simpan kembali ke database
            $event->whatsapp_config = $config;
            $event->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $event = Event::where('hardcoded', 'latbarkamis')->first();
        
        if ($event) {
            $config = $event->whatsapp_config ?? [];
            
            // Kembalikan ke default (true atau hapus key)
            unset($config['enabled']); 
            
            $event->whatsapp_config = $config;
            $event->save();
        }
    }
};
