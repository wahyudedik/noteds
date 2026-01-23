<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BenchmarkController extends Controller
{
    public function index(Request $request): Response
    {
        $json = [];
        if (Storage::exists('benchmarks/top_benchmark.json')) {
            $json = json_decode(Storage::get('benchmarks/top_benchmark.json'), true) ?: [];
        }
        return Inertia::render('Benchmarks/Top', ['rows' => $json]);
    }

    public function data(Request $request)
    {
        if (!Storage::exists('benchmarks/top_benchmark.json')) {
            return response()->json([]);
        }
        return response()->json(json_decode(Storage::get('benchmarks/top_benchmark.json'), true));
    }
}
