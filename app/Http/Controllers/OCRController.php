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
            'image' => ['required', 'image', 'max:5120'], // Max 5MB
        ]);

        try {
            $imagePath = $request->file('image')->getRealPath();
            $mimeType = $request->file('image')->getMimeType();
            $base64Image = base64_encode(file_get_contents($imagePath));

            $apiKey = env('OPENAI_API_KEY');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi OpenAI API Key belum tersedia.'
                ], 500);
            }

            $prompt = 'Anda adalah sistem ekstraksi OCR (Optical Character Recognition) khusus untuk KTP (Kartu Tanda Penduduk) Indonesia.
Tugas Anda adalah membaca gambar KTP ini dan mengekstrak datanya ke dalam format JSON yang valid dan baku. 

Aturan ekstraksi:
1. "nik": Ambil 16 digit angka NIK. Pastikan akurat.
2. "name": Ambil nama lengkap sesuai KTP. Abaikan gelar jika memungkinkan.
3. "date_of_birth": Ambil tanggal lahir (Tempat/Tgl Lahir). Formatkan menjadi MM-DD-YYYY. (Contoh jika tertulis JAKARTA, 17-08-1990 -> maka jadikan 08-17-1990).
4. "gender": Ambil jenis kelamin. Jika tertulis LAKI-LAKI kembalikan "male", jika PEREMPUAN kembalikan "female".

HANYA KEMBALIKAN OUTPUT JSON SAJA. Jika gambar bukan KTP atau buram, berikan respons JSON dengan key "error" bernilai pesan kesalahannya.';

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
                            'content' => [
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
                            ]
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
