<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\WhatsApp;
use App\Services\OpenAiService;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    public function handle(Request $request)
    {
        // 1. Validasi Token Keamanan (Opsional, misal: ?token=secret_key)
        $token = env('WHATSAPP_WEBHOOK_TOKEN');
        if ($token && $request->query('token') !== $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $from = $request->input('from'); // Nomor pengirim (misal: 628123456789)
        $message = trim($request->input('message')); // Isi pesan

        if (!$from || !$message) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Normalisasi nomor telepon ke format lokal/database
        $phone = preg_replace('/\D+/', '', $from);

        // Cari User berdasarkan nomor telepon
        $user = User::where('phone', 'like', "%$phone%")->first();

        $upperMessage = strtoupper($message);

        // Handle Opt-out / Opt-in
        if ($upperMessage === 'STOP') {
            if ($user) {
                $user->update(['is_receive_wa' => false]);
            }
            WhatsApp::send($from, "Anda telah berhasil berhenti menerima pengingat WhatsApp harian.");
            return response()->json(['success' => true]);
        } elseif (in_array($upperMessage, ['START', 'AKTIFKAN'])) {
            if ($user) {
                $user->update(['is_receive_wa' => true]);
            }
            WhatsApp::send($from, "Notifikasi WhatsApp harian Anda telah diaktifkan kembali.");
            return response()->json(['success' => true]);
        }

        // Gunakan ChatGPT (GPT-4) untuk membalas pesan terkait running
        $systemMessage = "Anda adalah AI Running Coach dari Ruang Lari. Tugas Anda adalah menjawab pertanyaan seputar olahraga lari, program latihan lari, nutrisi lari, cedera lari, pace, sepatu lari, dan hal-hal lain yang berkaitan langsung dengan running.\n\n"
            . "ATURAN UTAMA:\n"
            . "- Jawablah dengan ramah, santai, dan informatif menggunakan bahasa Indonesia.\n"
            . "- JANGAN menjawab pertanyaan yang tidak berkaitan dengan lari atau olahraga atletik. Jika pengguna bertanya di luar topik lari, tolak dengan sopan dan ingatkan mereka bahwa Anda hanya bisa membantu menjawab pertanyaan seputar lari.\n"
            . "- Jangan gunakan format markdown (seperti *bold* atau _miring_). Tulis teks polos saja.\n"
            . "- Jangan gunakan emoji sama sekali.";

        try {
            // Menggunakan model gpt-4
            $reply = $this->openAiService->getAiResponse($message, $systemMessage, 'gpt-4');

            if ($reply) {
                // Bersihkan format markdown dan emoji jika ada
                $reply = preg_replace('/[*_~`]+/', '', $reply);
                $reply = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2190}-\x{21FF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}]/u', '', $reply);
                $reply = preg_replace('/\s+/', ' ', $reply);
                $reply = trim($reply);

                WhatsApp::send($from, $reply);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Webhook AI Error: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
