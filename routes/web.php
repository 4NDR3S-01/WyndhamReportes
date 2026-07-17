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

// Descarga del documento original subido al módulo de cocina
Route::get('/cocina/documento/{id}/descargar', function (int $id) {
    $archivo = \App\Models\CocinaArchivoImportado::query()->findOrFail($id);

    if (! $archivo->ruta || ! \Illuminate\Support\Facades\Storage::exists($archivo->ruta)) {
        abort(404, 'El archivo físico no se encuentra en el almacenamiento.');
    }

    return response()->download(
        \Illuminate\Support\Facades\Storage::path($archivo->ruta),
        $archivo->nombre_original
    );
})->middleware(['auth'])->name('cocina.descargar.documento');
