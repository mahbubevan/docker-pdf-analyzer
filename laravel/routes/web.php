<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-python', function () {
    try {
        $response = Http::timeout(3)->get(config('services.python.url') . '/health');

        return [
            'laravel' => 'running',
            'python' => $response->json()
        ];
    } catch (\Throwable $th) {
        return response()->json([
            'laravel' => 'running',
            'python' => 'unavailable',
            'message' => $th->getMessage()
        ], 503);
    }
});

Route::get('/test-node', function () {
    try {
        $response = Http::timeout(3)->get(config('services.node.url') . '/health');

        return [
            'laravel' => 'running',
            'node' => $response->json()
        ];
    } catch (\Throwable $th) {
        return response()->json([
            'laravel' => 'running',
            'node' => 'unavailable',
            'message' => $th->getMessage()
        ], 503);
    }
});


Route::get('/test-node-event', function () {
    try {
        $data = [
            'analysis_id' => 1,
            'status' => 'processing',
            'progress' => 50,
            'message' => 'Laravel sent this event'
        ];
        $response = Http::timeout(3)->post(config('services.node.url') . '/event', $data);

        return [
            'laravel' => 'Event sent',
            'node' => $response->json()
        ];
    } catch (\Throwable $th) {
        return response()->json([
            'laravel' => 'running',
            'node' => 'unavailable',
            'message' => $th->getMessage()
        ], 503);
    }
});
