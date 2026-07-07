<?php
/**
 * Script de reparación: sincroniza el catálogo de medicamentos (Base Médica)
 * con el inventario (medico_productos).
 *
 * Acciones:
 *  1. Medicamentos sin producto → crea producto vinculado
 *  2. Productos (medicina) sin medicamento_id → busca/parea y vincula
 *  3. Nombres divergentes → unifica
 *  4. Reporta el estado final
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicamento;
use App\Models\MedicoProducto;
use App\Models\MedicoProductoAlias;

echo "╔══════════════════════════════════════════════╗\n";
echo "║  REPARACIÓN: Sincronizar Catálogo ↔ Inventario ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

$creados = 0;
$vinculados = 0;
$renombrados = 0;
$sinCambio = 0;

// ─────────────────────────────────────────────────
// 1. MEDICAMENTOS → PRODUCTOS
//    Cada medicamento activo debe tener un producto vinculado
// ─────────────────────────────────────────────────
echo "── 1. Medicamentos → Productos ──\n";

$medsSinProd = Medicamento::query()
    ->where('activo', true)
    ->whereDoesntHave('productos')
    ->get();

foreach ($medsSinProd as $med) {
    // Buscar producto con mismo nombre (sin vincular)
    $prod = MedicoProducto::query()
        ->whereNull('medicamento_id')
        ->where('nombre', $med->nombre)
        ->where('tipo', 'medicina')
        ->first();

    if ($prod) {
        $prod->update(['medicamento_id' => $med->id]);
        echo "  ✓ Vinculado: {$med->nombre}\n";
        $vinculados++;
        continue;
    }

    // Buscar por nombre normalizado
    $norm = MedicoProducto::normalizarNombre($med->nombre);
    $prod = MedicoProducto::query()
        ->whereNull('medicamento_id')
        ->where('tipo', 'medicina')
        ->get()
        ->first(fn($p) => MedicoProducto::normalizarNombre($p->nombre) === $norm);

    if ($prod) {
        $prod->update(['medicamento_id' => $med->id]);
        echo "  ✓ Vinculado (fuzzy): {$med->nombre} → [{$prod->id}] {$prod->nombre}\n";
        $vinculados++;
        continue;
    }

    // Crear producto nuevo
    MedicoProducto::query()->create([
        'medicamento_id' => $med->id,
        'tipo'           => 'medicina',
        'nombre'         => $med->nombre,
        'stock_minimo'   => 0,
        'activo'         => true,
    ]);
    echo "  + Creado: {$med->nombre}\n";
    $creados++;
}

if ($medsSinProd->isEmpty()) {
    echo "  Todos los medicamentos ya tienen producto vinculado ✓\n";
}

echo "\n";

// ─────────────────────────────────────────────────
// 2. PRODUCTOS → MEDICAMENTOS
//    Cada producto tipo 'medicina' debe tener medicamento_id
// ─────────────────────────────────────────────────
echo "── 2. Productos (medicina) sin vincular → Medicamentos ──\n";

$prodsSinVinc = MedicoProducto::query()
    ->whereNull('medicamento_id')
    ->where('tipo', 'medicina')
    ->where('activo', true)
    ->get();

foreach ($prodsSinVinc as $prod) {
    // Buscar medicamento con mismo nombre
    $med = Medicamento::query()->where('nombre', $prod->nombre)->first();

    if ($med) {
        $prod->update(['medicamento_id' => $med->id]);
        echo "  ✓ Vinculado: {$prod->nombre} → med_id={$med->id}\n";
        $vinculados++;
        continue;
    }

    // Buscar fuzzy
    $norm = MedicoProducto::normalizarNombre($prod->nombre);
    $candidatos = Medicamento::all();
    $mejor = null;
    $mejorDist = PHP_INT_MAX;

    foreach ($candidatos as $c) {
        $dist = levenshtein($norm, MedicoProducto::normalizarNombre($c->nombre));
        $maxLen = max(mb_strlen($norm), mb_strlen(MedicoProducto::normalizarNombre($c->nombre)));
        if ($maxLen > 3 && $dist < $mejorDist && $dist <= ceil($maxLen * 0.2)) {
            $mejorDist = $dist;
            $mejor = $c;
        }
    }

    if ($mejor) {
        $prod->update(['medicamento_id' => $mejor->id]);
        echo "  ✓ Vinculado (fuzzy d={$mejorDist}): {$prod->nombre} → {$mejor->nombre}\n";
        $vinculados++;
        continue;
    }

    // Crear medicamento nuevo
    Medicamento::query()->create([
        'nombre' => $prod->nombre,
        'activo' => true,
    ]);
    // Volver a buscar para vincular
    $nuevo = Medicamento::query()->where('nombre', $prod->nombre)->first();
    if ($nuevo) {
        $prod->update(['medicamento_id' => $nuevo->id]);
        echo "  + Medicamento creado y vinculado: {$prod->nombre}\n";
        $creados++;
    }
}

if ($prodsSinVinc->isEmpty()) {
    echo "  Todos los productos (medicina) ya están vinculados ✓\n";
}

echo "\n";

// ─────────────────────────────────────────────────
// 3. NOMBRES DIVERGENTES
//    Producto vinculado pero con nombre ≠ medicamento
// ─────────────────────────────────────────────────
echo "── 3. Unificar nombres divergentes ──\n";

$prodsConLink = MedicoProducto::query()
    ->whereNotNull('medicamento_id')
    ->where('tipo', 'medicina')
    ->get();

foreach ($prodsConLink as $prod) {
    $med = $prod->medicamento;
    if (! $med) {
        // medicamento_id huérfano
        $prod->update(['medicamento_id' => null]);
        echo "  ⚠ Limpiado vínculo huérfano: [{$prod->id}] {$prod->nombre}\n";
        continue;
    }

    if ($med->nombre !== $prod->nombre) {
        // El nombre del medicamento prevalece (es el catálogo oficial)
        echo "  ↻ Renombrado: {$prod->nombre} → {$med->nombre}\n";
        $prod->update(['nombre' => $med->nombre]);
        $renombrados++;
    } else {
        $sinCambio++;
    }
}

echo "  {$sinCambio} nombres coinciden, {$renombrados} corregidos\n\n";

// ─────────────────────────────────────────────────
// RESUMEN FINAL
// ─────────────────────────────────────────────────
echo "══════════════════════════════════════════════\n";
echo "  RESUMEN FINAL\n";
echo "══════════════════════════════════════════════\n";
echo "Medicamentos totales:    " . Medicamento::count() . " (activos: " . Medicamento::where('activo', true)->count() . ")\n";
echo "Productos totales:       " . MedicoProducto::count() . " (activos: " . MedicoProducto::where('activo', true)->count() . ")\n";
echo "  - Medicinas:           " . MedicoProducto::where('tipo', 'medicina')->count() . "\n";
echo "  - Equipos:             " . MedicoProducto::where('tipo', 'insumo')->count() . "\n";
echo "Productos CON vínculo:   " . MedicoProducto::whereNotNull('medicamento_id')->count() . "\n";
echo "Productos SIN vínculo:   " . MedicoProducto::whereNull('medicamento_id')->count() . "\n";
echo "Medicamentos sin prod:   " . Medicamento::where('activo', true)->whereDoesntHave('productos')->count() . "\n\n";
echo "Acciones realizadas:\n";
echo "  + Creados:             {$creados}\n";
echo "  ✓ Vinculados:          {$vinculados}\n";
echo "  ↻ Renombrados:         {$renombrados}\n";
