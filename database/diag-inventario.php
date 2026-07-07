<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicamento;
use App\Models\MedicoProducto;
use App\Models\MedicoKardex;

echo "══════════════════════════════════════════════\n";
echo "  DIAGNÓSTICO: Inventario vs Base Médica\n";
echo "══════════════════════════════════════════════\n\n";

// ── CONTEO GENERAL ──
echo "=== MEDICAMENTOS (catálogo clínico) ===\n";
echo "Total: " . Medicamento::count() . "\n";
echo "Activos: " . Medicamento::where('activo', true)->count() . "\n";
echo "Inactivos: " . Medicamento::where('activo', false)->count() . "\n\n";

echo "=== MEDICO_PRODUCTOS (inventario) ===\n";
echo "Total: " . MedicoProducto::count() . "\n";
echo "Activos: " . MedicoProducto::where('activo', true)->count() . "\n";
echo "Inactivos: " . MedicoProducto::where('activo', false)->count() . "\n";
echo "  Medicinas: " . MedicoProducto::where('tipo', 'medicina')->count() . "\n";
echo "  Equipos: " . MedicoProducto::where('tipo', 'insumo')->count() . "\n\n";

// ── VÍNCULOS ──
echo "=== VÍNCULOS medicamento_id ===\n";
$conVinculo = MedicoProducto::whereNotNull('medicamento_id')->count();
$sinVinculo = MedicoProducto::whereNull('medicamento_id')->count();
echo "Productos CON medicamento_id: {$conVinculo}\n";
echo "Productos SIN medicamento_id: {$sinVinculo}\n\n";

// ── SIN VINCULAR ──
echo "=== PRODUCTOS (medicina) SIN medicamento_id ===\n";
$sinVinc = MedicoProducto::whereNull('medicamento_id')->where('tipo', 'medicina')->get();
if ($sinVinc->isNotEmpty()) {
    foreach ($sinVinc as $p) {
        echo "  [{$p->id}] {$p->nombre} (activo=" . ($p->activo ? 'sí' : 'no') . ")\n";
    }
} else {
    echo "  Ninguno — todas las medicinas están vinculadas ✓\n";
}
echo "\n";

// ── EQUIPOS ──
echo "=== EQUIPOS (no deberían tener medicamento_id) ===\n";
$equipos = MedicoProducto::where('tipo', 'insumo')->get();
foreach ($equipos as $eq) {
    $link = $eq->medicamento_id ? " → med_id={$eq->medicamento_id}" : " (sin vincular)";
    echo "  [{$eq->id}] {$eq->nombre}{$link}\n";
}
echo "\n";

// ── MEDICAMENTOS SIN PRODUCTO ──
echo "=== MEDICAMENTOS activos SIN producto vinculado ===\n";
$sinProd = Medicamento::where('activo', true)
    ->whereDoesntHave('productos')
    ->get();
if ($sinProd->isNotEmpty()) {
    foreach ($sinProd as $m) {
        echo "  [{$m->id}] {$m->nombre}\n";
    }
    echo "  → Total: {$sinProd->count()} medicamentos sin producto en inventario\n";
} else {
    echo "  Ninguno — todos tienen producto ✓\n";
}
echo "\n";

// ── DUPLICADOS POR NOMBRE (normalizado) ──
echo "=== POSIBLES DUPLICADOS (mismo nombre normalizado) ===\n";
$prods = MedicoProducto::all();
$agrupados = [];
foreach ($prods as $p) {
    $norm = MedicoProducto::normalizarNombre($p->nombre);
    $agrupados[$norm][] = $p;
}
$dupes = array_filter($agrupados, fn($g) => count($g) > 1);
if ($dupes) {
    foreach ($dupes as $norm => $grupo) {
        echo "  Nombre normalizado: '{$norm}'\n";
        foreach ($grupo as $p) {
            echo "    [{$p->id}] {$p->nombre} (tipo={$p->tipo}, activo=" . ($p->activo ? 'sí' : 'no') . ")\n";
        }
    }
} else {
    echo "  No se encontraron duplicados ✓\n";
}
echo "\n";

// ── NOMBRES QUE DIFIEREN ENTRE medicamentos Y medico_productos ──
echo "=== MEDICAMENTOS vs PRODUCTOS: diferencias de nombre ===\n";
$meds = Medicamento::where('activo', true)->get()->keyBy('id');
$prodsConLink = MedicoProducto::whereNotNull('medicamento_id')->get();
$diferencias = 0;
foreach ($prodsConLink as $prod) {
    $med = $meds->get($prod->medicamento_id);
    if ($med && $med->nombre !== $prod->nombre) {
        $diferencias++;
        if ($diferencias <= 10) {
            echo "  Medicamento: {$med->nombre}\n";
            echo "  Producto:    {$prod->nombre}\n\n";
        }
    }
}
if ($diferencias > 10) {
    echo "  ... y " . ($diferencias - 10) . " más\n";
}
if ($diferencias === 0) {
    echo "  Todos los nombres coinciden ✓\n";
}

// ── DETALLE DE PRODUCTOS SIN VINCULAR ──
echo "\n=== DETALLE: Productos SIN medicamento_id (unlinked) ===\n";
$sinVincTodos = MedicoProducto::whereNull('medicamento_id')->get();
$porTipo = $sinVincTodos->groupBy('tipo');
foreach ($porTipo as $tipo => $grupo) {
    echo "  [{$tipo}] " . $grupo->count() . " productos:\n";
    foreach ($grupo as $p) {
        $estado = $p->activo ? 'activo' : 'inactivo';
        echo "    - [{$p->id}] {$p->nombre} ({$estado})\n";
    }
}
if ($sinVincTodos->isEmpty()) {
    echo "  Ninguno — todo vinculado ✓\n";
}

echo "\n══════════════════════════════════════════════\n";
echo "  RESUMEN\n";
echo "══════════════════════════════════════════════\n";
echo "Base Médica (medicamentos): " . Medicamento::where('activo', true)->count() . " activos\n";
echo "Inventario (productos):     " . MedicoProducto::where('activo', true)->count() . " activos\n";
echo "  - Medicinas vinculadas:   " . MedicoProducto::where('activo', true)->where('tipo', 'medicina')->whereNotNull('medicamento_id')->count() . "\n";
echo "  - Medicinas sin vincular: " . MedicoProducto::where('activo', true)->where('tipo', 'medicina')->whereNull('medicamento_id')->count() . "\n";
echo "  - Equipos:                " . MedicoProducto::where('activo', true)->where('tipo', 'insumo')->count() . "\n";
echo "Medicamentos sin producto:  " . Medicamento::where('activo', true)->whereDoesntHave('productos')->count() . "\n";
