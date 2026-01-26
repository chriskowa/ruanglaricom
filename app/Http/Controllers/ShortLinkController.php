<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortLinkController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');
        
        // Cek jika URL sudah ada di DB untuk menghemat space
        $existing = ShortLink::where('original_url', $url)->first();
        if ($existing) {
            return response()->json([
                'short_url' => route('shortlink.redirect', $existing->code),
                'code' => $existing->code
            ]);
        }

        // Generate unique code
        do {
            $code = Str::random(6);
        } while (ShortLink::where('code', $code)->exists());

        $link = ShortLink::create([
            'code' => $code,
            'original_url' => $url
        ]);

        return response()->json([
            'short_url' => route('shortlink.redirect', $link->code),
            'code' => $link->code
        ]);
    }

    public function redirect($code)
    {
        $link = ShortLink::where('code', $code)->firstOrFail();
        return redirect($link->original_url);
    }
}
