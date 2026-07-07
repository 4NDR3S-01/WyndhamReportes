<?php
/**
 * Corrige la clasificación de productos vinculados a medicamentos.
 * Todo producto con medicamento_id debe ser tipo 'medicina'.
 * Además sincroniza nombres y estados entre ambas tablas.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicamento;
use App\Models\MedicoProducto;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════╗\n";
echo "║  CORRECCIÓN: Tipos, nombres y vínculos          ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

$corregidos = 0;
$renombrados = 0;
$creados = 0;
$activados = 0;

// ── 1. Corregir TIPO de productos vinculados a medicamentos ──
echo "── 1. Productos vinculados con tipo ≠ 'medicina' ──\n";
$malClasificados = MedicoProducto::query()
    ->whereNotNull('medicamento_id')
    ->where('tipo', '!=', 'medicina')
    ->get();

foreach ($malClasificados as $prod) {
    $tipoAnterior = $prod->tipo;
    $prod->update(['tipo' => 'medicina']);
    echo "  ↻ [{$prod->id}] {$prod->nombre}: tipo '{$tipoAnterior}' → 'medicina'\n";
    $corregidos++;
}
if ($malClasificados->isEmpty()) {
    echo "  Todos correctos ✓\n";
}

// ── 2. Sincronizar NOMBRES: producto → medicamento ──
echo "\n── 2. Sincronizar nombres ──\n";
$vinculados = MedicoProducto::query()
    ->with('medicamento')
    ->whereNotNull('medicamento_id')
    ->get();

foreach ($vinculados as $prod) {
    if (! $prod->medicamento) {
        echo "  ⚠ [{$prod->id}] {$prod->nombre}: medicamento_id={$prod->medicamento_id} no existe → limpiando\n";
        $prod->update(['medicamento_id' => null]);
        continue;
    }

    // Si el nombre del producto es más específico (ej: "PARACETAMOL 500MG" vs "PARACETAMOL"),
    // mantener el del producto y actualizar el medicamento
    if ($prod->nombre !== $prod->medicamento->nombre) {
        $oldName = $prod->medicamento->nombre;
        $prod->medicamento->update(['nombre' => $prod->nombre]);
        echo "  ↻ Medicamento '{$oldName}' → '{$prod->nombre}'\n";
        $renombrados++;
    }

    // Sincronizar estado activo
    if ($prod->activo !== $prod->medicamento->activo) {
        $prod->medicamento->update(['activo' => $prod->activo]);
        echo "  ↻ Medicamento '{$prod->nombre}' activo → " . ($prod->activo ? 'sí' : 'no') . "\n";
        $activados++;
    }
}

// ── 3. Medicamentos SIN producto → crear ──
echo "\n── 3. Medicamentos activos sin producto ──\n";
$medsSinProd = Medicamento::query()
    ->where('activo', true)
    ->whereDoesntHave('productos')
    ->get();

foreach ($medsSinProd as $med) {
    MedicoProducto::query()->create([
        'medicamento_id' => $med->id,
        'tipo'           => 'medicina',
        'nombre'         => $med->nombre,
        'stock_minimo'   => 0,
        'activo'         => true,
    ]);
    echo "  + Producto creado: {$med->nombre}\n";
    $creados++;
}
if ($medsSinProd->isEmpty()) {
    echo "  Todos tienen producto ✓\n";
}

// ── 4. TODOS los productos sin medicamento_id → crear medicamento ──
echo "\n── 4. Todos los productos sin vincular → crear medicamento ──\n";
$prodsSinLink = MedicoProducto::query()
    ->whereNull('medicamento_id')
    ->where('activo', true)
    ->get();

foreach ($prodsSinLink as $prod) {
    $med = Medicamento::query()->firstOrCreate(
        ['nombre' => $prod->nombre],
        ['activo' => true]
    );
    $prod->update(['medicamento_id' => $med->id]);
    echo "  + Medicamento creado y vinculado: [{$prod->tipo}] {$prod->nombre}\n";
    $creados++;
}
if ($prodsSinLink->isEmpty()) {
    echo "  Todos vinculados ✓\n";
}

// ── RESUMEN ──
echo "\n══════════════════════════════════════════════════\n";
echo "  RESUMEN FINAL\n";
echo "══════════════════════════════════════════════════\n";

$totalMeds = Medicamento::where('activo', true)->count();
$totalProds = MedicoProducto::where('activo', true)->count();
$medicinas = MedicoProducto::where('tipo', 'medicina')->count();
$equipos = MedicoProducto::where('tipo', 'insumo')->count();
$insumos = MedicoProducto::where('tipo', 'insumo')->count();
$conVinculo = MedicoProducto::whereNotNull('medicamento_id')->count();
$sinVinculo = MedicoProducto::whereNull('medicamento_id')->count();

echo "Medicamentos (catálogo):  {$totalMeds}\n";
echo "Productos (inventario):   {$totalProds}\n";
echo "  - Medicinas vinculadas: {$conVinculo} (deben coincidir con medicamentos)\n";
echo "  - Equipos:              {$equipos}\n";
echo "  - Insumos:              {$insumos}\n";
echo "  - Sin vincular:         {$sinVinculo} (equipos/insumos sin medicamento)\n";
echo "\n";
echo "✅ Coherencia: " . ($totalMeds === $conVinculo ? "Medicamentos ({$totalMeds}) = Productos vinculados ({$conVinculo}) ✓" : "⚠ Aún hay discrepancia") . "\n";
echo "\n";
echo "Acciones:\n";
echo "  ↻ Tipos corregidos:     {$corregidos}\n";
echo "  ↻ Nombres unificados:   {$renombrados}\n";
echo "  ↻ Estados activados:    {$activados}\n";
echo "  + Creados:              {$creados}\n";
