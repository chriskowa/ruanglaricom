<?php

namespace App\Http\Controllers;

use App\Models\Pacer;
use Illuminate\Http\Request;

class PacerController extends Controller
{
    public function index()
    {
        $pacers = Pacer::with('user')->orderBy('verified', 'desc')->orderBy('total_races', 'desc')->get();
        return view('pacer.index', compact('pacers'));
    }

    public function show(string $slug)
    {
        $pacer = Pacer::with('user')->where('seo_slug', $slug)->firstOrFail();
        return view('pacer.profile', compact('pacer'));
    }
}

