<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MedicoProducto;
use App\Models\MedicoKardex;
use Illuminate\Support\Facades\DB;

echo "=== Renombrar 'equipo' → 'insumo' ===\n\n";

// BD: actualizar medico_productos
$prodCount = MedicoProducto::where('tipo', 'equipo')->update(['tipo' => 'insumo']);
echo "Productos actualizados: {$prodCount}\n";

// BD: actualizar medico_kardex
$kardexCount = MedicoKardex::where('tipo', 'equipo')->update(['tipo' => 'insumo']);
echo "Kardex actualizados: {$kardexCount}\n";

// Verificar
echo "\nDistribución actual:\n";
foreach (MedicoProducto::select('tipo', DB::raw('count(*) as total'))->groupBy('tipo')->get() as $g) {
    echo "  {$g->tipo}: {$g->total}\n";
}
echo "\nListo ✓\n";
