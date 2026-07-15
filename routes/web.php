<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Descarga de archivos temporales generados (reportes, etc.)
Route::get('/descargar/{file}', function (string $file) {
    $path = storage_path("app/temp/{$file}");

    if (! file_exists($path) || ! preg_match('/^[\w.-]+$/', $file)) {
        abort(404);
    }

    return response()->download($path)->deleteFileAfterSend();
})->middleware(['auth'])->name('descargar.temp');

// Visualización inline de archivos HTML temporales
Route::get('/ver/{file}', function (string $file) {
    $path = storage_path("app/temp/{$file}");

    if (! file_exists($path) || ! preg_match('/^[\w.-]+$/', $file)) {
        abort(404);
    }

    return response()->file($path, ['Content-Type' => 'text/html'])->deleteFileAfterSend();
})->middleware(['auth'])->name('ver.temp');
