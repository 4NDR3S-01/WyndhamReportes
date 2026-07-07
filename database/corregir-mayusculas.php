<?php
/**
 * CORRECCIÓN: Unificar formato a MAYÚSCULAS y corregir errores de escritura
 *
 * Acciones:
 *  1. Corregir typos conocidos (Equipode → EQUIPO DE, etc.)
 *  2. Convertir TODOS los nombres a MAYÚSCULAS (medicamentos + productos)
 *  3. Convertir tipo=equipo → tipo=insumo en medico_productos y medico_kardex
 *  4. Re-sincronizar nombres entre medicamento ↔ producto vinculado
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicamento;
use App\Models\MedicoProducto;
use App\Models\MedicoKardex;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  CORRECCIÓN: MAYÚSCULAS + typos + equipo→insumo         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$corregidosTypo = 0;
$corregidosMayus = 0;
$corregidosTipo = 0;
$corregidosSync = 0;

// ── 0. Correcciones de typos conocidos ──
echo "── 0. Corrección de typos ──\n";
$typos = [
    'Equipode Sutura'       => 'EQUIPO DE SUTURA',
    'equipode sutura'       => 'EQUIPO DE SUTURA',
    'EQUIPODE SUTURA'       => 'EQUIPO DE SUTURA',
];

foreach ($typos as $mal => $bien) {
    // medicamentos
    $count = Medicamento::where('nombre', $mal)->update(['nombre' => $bien]);
    if ($count) echo "  ↻ Medicamento: '{$mal}' → '{$bien}'\n";
    $corregidosTypo += $count;

    // productos
    $count = MedicoProducto::where('nombre', $mal)->update(['nombre' => $bien]);
    if ($count) echo "  ↻ Producto: '{$mal}' → '{$bien}'\n";
    $corregidosTypo += $count;
}
if ($corregidosTypo === 0) echo "  No se encontraron typos\n";
echo "\n";

// ── 1. Convertir medicamentos a MAYÚSCULAS ──
echo "── 1. Medicamentos → MAYÚSCULAS ──\n";
$meds = Medicamento::all();
foreach ($meds as $med) {
    $upper = mb_strtoupper(trim($med->nombre));
    if ($med->nombre !== $upper) {
        echo "  ↻ [{$med->id}] {$med->nombre} → {$upper}\n";
        $med->update(['nombre' => $upper]);
        $corregidosMayus++;
    }
}
if ($corregidosMayus === 0) echo "  Todos ya en MAYÚSCULAS ✓\n";
echo "\n";

// ── 2. Convertir productos a MAYÚSCULAS ──
echo "── 2. Productos → MAYÚSCULAS ──\n";
$prods = MedicoProducto::all();
foreach ($prods as $prod) {
    $upper = mb_strtoupper(trim($prod->nombre));
    if ($prod->nombre !== $upper) {
        echo "  ↻ [{$prod->id}] {$prod->nombre} → {$upper}\n";
        $prod->update(['nombre' => $upper]);
        $corregidosMayus++;
    }
}
if ($corregidosMayus === 0) echo "  Todos ya en MAYÚSCULAS ✓\n";
echo "\n";

// ── 3. Convertir tipo=equipo → tipo=insumo ──
echo "── 3. Convertir tipo 'equipo' → 'insumo' ──\n";
$prodCount = MedicoProducto::where('tipo', 'equipo')->update(['tipo' => 'insumo']);
if ($prodCount) echo "  ↻ Productos: {$prodCount} registros actualizados\n";
$corregidosTipo += $prodCount;

$kardexCount = MedicoKardex::where('tipo', 'equipo')->update(['tipo' => 'insumo']);
if ($kardexCount) echo "  ↻ Kardex: {$kardexCount} registros actualizados\n";
$corregidosTipo += $kardexCount;

if ($prodCount === 0 && $kardexCount === 0) echo "  Ya están convertidos ✓\n";
echo "\n";

// ── 4. Re-sincronizar nombres entre productos y medicamentos vinculados ──
echo "── 4. Sincronizar nombres medicamento ↔ producto ──\n";
$vinculados = MedicoProducto::with('medicamento')->whereNotNull('medicamento_id')->get();
foreach ($vinculados as $prod) {
    if (! $prod->medicamento) continue;

    // El medicamento manda (es el catálogo maestro)
    if ($prod->nombre !== $prod->medicamento->nombre) {
        $old = $prod->nombre;
        $prod->update(['nombre' => $prod->medicamento->nombre]);
        echo "  ↻ Producto [{$prod->id}]: '{$old}' → '{$prod->medicamento->nombre}'\n";
        $corregidosSync++;
    }
}
if ($corregidosSync === 0) echo "  Todos sincronizados ✓\n";
echo "\n";

// ── RESUMEN ──
echo "══════════════════════════════════════════════════════════\n";
echo "  RESUMEN\n";
echo "══════════════════════════════════════════════════════════\n";
echo "Typos corregidos:         {$corregidosTypo}\n";
echo "Nombres → MAYÚSCULAS:     {$corregidosMayus}\n";
echo "Tipo 'equipo' → 'insumo': {$corregidosTipo}\n";
echo "Sincronizaciones:         {$corregidosSync}\n\n";

echo "Medicamentos totales:     " . Medicamento::count() . "\n";
echo "Productos totales:        " . MedicoProducto::count() . "\n";
echo "  - Medicinas:            " . MedicoProducto::where('tipo', 'medicina')->count() . "\n";
echo "  - Insumos:              " . MedicoProducto::where('tipo', 'insumo')->count() . "\n";
echo "  - Con medicamento_id:   " . MedicoProducto::whereNotNull('medicamento_id')->count() . "\n";
echo "  - Sin medicamento_id:   " . MedicoProducto::whereNull('medicamento_id')->count() . "\n";
echo "\n✅ Formato unificado. Todos en MAYÚSCULAS.\n";
