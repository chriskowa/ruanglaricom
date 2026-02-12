<?php

namespace App\Http\Controllers;

class CalculatorController extends Controller
{
    public function index()
    {
        return view('tools.calculator');
    }
}
