<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OCRController extends Controller
{
    public function scanKTP(Request $request)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'max:5120'],
            'text'  => ['nullable', 'string'],
        ]);

        if (!$request->hasFile('image') && !$request->filled('text')) {
            return response()->json([
                'success' => false,
                'message' => 'Harap sertakan gambar KTP atau teks hasil OCR.'
            ], 422);
        }

        try {
            $apiKey = env('OPENAI_API_KEY');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi OpenAI API Key belum tersedia.'
                ], 500);
            }

            $prompt = 'Anda adalah sistem ekstraksi data khusus untuk KTP (Kartu Tanda Penduduk) Indonesia.
Tugas Anda adalah membaca data KTP ini dan mengekstraknya ke dalam format JSON yang valid dan baku. 

Aturan ekstraksi:
1. "nik": Ambil 16 digit angka NIK. Koreksi typo umum (misal O/o jadi 0, I/l jadi 1). Pastikan akurat.
2. "name": Ambil nama lengkap sesuai KTP. Abaikan gelar jika memungkinkan.
3. "date_of_birth": Ambil tanggal lahir (Tempat/Tgl Lahir). Formatkan menjadi MM-DD-YYYY. (Contoh jika tertulis JAKARTA, 17-08-1990 -> maka jadikan 08-17-1990).
4. "gender": Ambil jenis kelamin. Jika tertulis LAKI-LAKI kembalikan "male", jika PEREMPUAN kembalikan "female".
5. "address": Ambil alamat lengkap beserta RT/RW, Kel/Desa, dan Kecamatan. Gabungkan menjadi satu kalimat string.

HANYA KEMBALIKAN OUTPUT JSON SAJA. Ekstrak data yang Anda temukan semaksimal mungkin. Jika data NIK, nama, atau alamat kurang lengkap, tetap kembalikan data yang berhasil diekstrak dan biarkan field lainnya kosong (null). Hanya kembalikan key "error" jika input benar-benar kosong atau sama sekali tidak berisi teks yang berkaitan dengan KTP Indonesia.';

            $messagesContent = [];

            if ($request->filled('text')) {
                // Gunakan mode Text Completion (Sangat murah token)
                $messagesContent = $prompt . "\n\nBERIKUT ADALAH TEKS KASAR HASIL SCAN OCR LOKAL:\n---\n" . $request->input('text') . "\n---\n";
            } else {
                // Fallback: Gunakan mode Vision (Lebih mahal)
                $imagePath = $request->file('image')->getRealPath();
                $mimeType = $request->file('image')->getMimeType();
                $base64Image = base64_encode(file_get_contents($imagePath));

                $messagesContent = [
                    [
                        'type' => 'text',
                        'text' => $prompt
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}"
                        ]
                    ]
                ];
            }

            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah asisten OCR spesialis KTP Indonesia yang hanya membalas dalam format JSON murni.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $messagesContent
                        ]
                    ],
                    'max_tokens' => 300,
                    'temperature' => 0.0
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $data = json_decode($content, true);

                if (isset($data['error'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gambar buram atau bukan KTP: ' . $data['error']
                    ], 422);
                }

                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }

            Log::error('OpenAI API Error (OCR): ' . $response->body());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca KTP. Silakan input manual.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('OCR Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses gambar.'
            ], 500);
        }
    }
}
