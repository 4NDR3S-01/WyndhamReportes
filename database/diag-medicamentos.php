<?php
/**
 * DIAGNÓSTICO: Revisión exhaustiva de medicamentos
 * - Duplicados (mismo nombre, nombre normalizado, fuzzy)
 * - Consistencia mayúsculas
 * - Sincronización catálogo ↔ inventario
 * - Productos huérfanos o mal vinculados
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Medicamento;
use App\Models\MedicoProducto;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  DIAGNÓSTICO: Medicamentos — Duplicados y Consistencia  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ────────────────────────────────────────────────────────
// 1. LISTADO COMPLETO DE MEDICAMENTOS
// ────────────────────────────────────────────────────────
echo "── 1. Todos los medicamentos (catálogo) ──\n";
$meds = Medicamento::orderBy('nombre')->get();
printf("%-4s %-50s %-10s %s\n", 'ID', 'NOMBRE', 'ACTIVO', 'TIENE PRODUCTO');
echo str_repeat('─', 80) . "\n";
foreach ($meds as $m) {
    $prodCount = $m->productos()->count();
    printf("%-4d %-50s %-10s %s\n",
        $m->id, mb_substr($m->nombre, 0, 48), $m->activo ? '✓' : '✗',
        $prodCount > 0 ? "sí ({$prodCount})" : 'NO ⚠'
    );
}
echo "Total: {$meds->count()} medicamentos\n\n";

// ────────────────────────────────────────────────────────
// 2. DUPLICADOS POR NOMBRE EXACTO (case-insensitive)
// ────────────────────────────────────────────────────────
echo "── 2. Duplicados por nombre exacto (case-insensitive) ──\n";
$agrupados = [];
foreach ($meds as $m) {
    $key = mb_strtolower(trim($m->nombre));
    $agrupados[$key][] = $m;
}
$dupesExacto = array_filter($agrupados, fn($g) => count($g) > 1);
if ($dupesExacto) {
    foreach ($dupesExacto as $key => $grupo) {
        echo "  ⚠ DUPLICADO: '{$key}'\n";
        foreach ($grupo as $m) {
            echo "    [{$m->id}] {$m->nombre} (activo=" . ($m->activo ? 'sí' : 'no') . ")\n";
        }
    }
    echo "  Total grupos duplicados: " . count($dupesExacto) . "\n";
} else {
    echo "  ✓ No hay duplicados exactos\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 3. DUPLICADOS POR NOMBRE NORMALIZADO
// ────────────────────────────────────────────────────────
echo "── 3. Duplicados por nombre normalizado ──\n";
$normGroup = [];
foreach ($meds as $m) {
    $norm = MedicoProducto::normalizarNombre($m->nombre);
    $normGroup[$norm][] = $m;
}
$dupesNorm = array_filter($normGroup, fn($g) => count($g) > 1);
if ($dupesNorm) {
    foreach ($dupesNorm as $norm => $grupo) {
        echo "  ⚠ GRUPO normalizado: '{$norm}'\n";
        foreach ($grupo as $m) {
            echo "    [{$m->id}] {$m->nombre} (activo=" . ($m->activo ? 'sí' : 'no') . ")\n";
        }
    }
    echo "  Total grupos: " . count($dupesNorm) . "\n";
} else {
    echo "  ✓ No hay duplicados normalizados\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 4. FUZZY DUPLICADOS (Levenshtein ≤ 2, nombres distintos)
// ────────────────────────────────────────────────────────
echo "── 4. Posibles duplicados fuzzy (distancia Levenshtein ≤ 2) ──\n";
$nombres = $meds->pluck('nombre', 'id')->toArray();
$revisados = [];
$fuzzyEncontrados = 0;
foreach ($nombres as $id1 => $n1) {
    $norm1 = MedicoProducto::normalizarNombre($n1);
    foreach ($nombres as $id2 => $n2) {
        if ($id1 >= $id2) continue;
        if (isset($revisados["{$id1}-{$id2}"])) continue;
        $revisados["{$id1}-{$id2}"] = true;

        $norm2 = MedicoProducto::normalizarNombre($n2);
        $dist = levenshtein($norm1, $norm2);
        $maxLen = max(mb_strlen($norm1), mb_strlen($norm2));
        // Solo reportar si son cercanos pero no idénticos
        if ($dist > 0 && $dist <= 2 && $maxLen > 3) {
            $fuzzyEncontrados++;
            if ($fuzzyEncontrados <= 15) {
                echo "  ? Distancia {$dist}: [{$id1}] {$n1}  ↔  [{$id2}] {$n2}\n";
            }
        }
    }
}
if ($fuzzyEncontrados === 0) {
    echo "  ✓ No se encontraron posibles duplicados fuzzy\n";
} elseif ($fuzzyEncontrados > 15) {
    echo "  ... y " . ($fuzzyEncontrados - 15) . " más\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 5. CONSISTENCIA DE MAYÚSCULAS
// ────────────────────────────────────────────────────────
echo "── 5. Nombres que NO están en MAYÚSCULAS ──\n";
$noMayus = $meds->filter(fn($m) => $m->nombre !== mb_strtoupper($m->nombre));
if ($noMayus->isNotEmpty()) {
    foreach ($noMayus as $m) {
        echo "  ⚠ [{$m->id}] {$m->nombre}\n";
    }
    echo "  Total: {$noMayus->count()} medicamentos con formato inconsistente\n";
} else {
    echo "  ✓ Todos los nombres están en MAYÚSCULAS\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 6. PRODUCTOS: nombres con formato inconsistente
// ────────────────────────────────────────────────────────
echo "── 6. Productos (inventario) con formato no MAYÚSCULAS ──\n";
$prods = MedicoProducto::all();
$prodNoMayus = $prods->filter(fn($p) => $p->nombre !== mb_strtoupper($p->nombre));
if ($prodNoMayus->isNotEmpty()) {
    foreach ($prodNoMayus as $p) {
        echo "  ⚠ [{$p->id}] {$p->nombre} (tipo={$p->tipo})\n";
    }
    echo "  Total: {$prodNoMayus->count()} productos con formato inconsistente\n";
} else {
    echo "  ✓ Todos los productos están en MAYÚSCULAS\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 7. SINCRONIZACIÓN: Medicamento ↔ Producto
// ────────────────────────────────────────────────────────
echo "── 7. Medicamentos activos SIN producto vinculado ──\n";
$sinProd = Medicamento::where('activo', true)
    ->whereDoesntHave('productos')
    ->get();
if ($sinProd->isNotEmpty()) {
    foreach ($sinProd as $m) {
        echo "  ⚠ [{$m->id}] {$m->nombre} — no tiene producto en inventario\n";
    }
    echo "  Total: {$sinProd->count()} medicamentos huérfanos\n";
} else {
    echo "  ✓ Todos los medicamentos activos tienen producto\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 8. Productos vinculados con nombre ≠ medicamento
// ────────────────────────────────────────────────────────
echo "── 8. Productos con nombre diferente a su medicamento ──\n";
$divergentes = 0;
$vinculados = MedicoProducto::with('medicamento')->whereNotNull('medicamento_id')->get();
foreach ($vinculados as $p) {
    if (!$p->medicamento) continue;
    if ($p->nombre !== $p->medicamento->nombre) {
        $divergentes++;
        if ($divergentes <= 10) {
            echo "  ↻ [{$p->id}] Prod: {$p->nombre}\n";
            echo "         Med:  {$p->medicamento->nombre}\n";
        }
    }
}
if ($divergentes > 10) echo "  ... y " . ($divergentes - 10) . " más\n";
if ($divergentes === 0) echo "  ✓ Todos los nombres coinciden\n";
echo "  Total divergencias: {$divergentes}\n\n";

// ────────────────────────────────────────────────────────
// 9. Productos sin medicamento_id (medicinas)
// ────────────────────────────────────────────────────────
echo "── 9. Productos tipo 'medicina' SIN medicamento_id ──\n";
$medSinLink = MedicoProducto::where('tipo', 'medicina')
    ->whereNull('medicamento_id')
    ->get();
if ($medSinLink->isNotEmpty()) {
    foreach ($medSinLink as $p) {
        echo "  ⚠ [{$p->id}] {$p->nombre}\n";
    }
    echo "  Total: {$medSinLink->count()} medicinas sin vincular\n";
} else {
    echo "  ✓ Todas las medicinas están vinculadas\n";
}
echo "\n";

// ────────────────────────────────────────────────────────
// 10. RESUMEN FINAL
// ────────────────────────────────────────────────────────
echo "══════════════════════════════════════════════════════════\n";
echo "  RESUMEN\n";
echo "══════════════════════════════════════════════════════════\n";
echo "Medicamentos totales:          {$meds->count()}\n";
echo "Medicamentos activos:          " . $meds->where('activo', true)->count() . "\n";
echo "Medicamentos inactivos:        " . $meds->where('activo', false)->count() . "\n";
echo "Duplicados exactos:            " . count($dupesExacto) . " grupos\n";
echo "Duplicados normalizados:       " . count($dupesNorm) . " grupos\n";
echo "Posibles fuzzy:                {$fuzzyEncontrados} pares\n";
echo "Formato no mayúsculas (med):   {$noMayus->count()}\n";
echo "Formato no mayúsculas (prod):  {$prodNoMayus->count()}\n";
echo "Medicamentos sin producto:     {$sinProd->count()}\n";
echo "Divergencias de nombre:        {$divergentes}\n";
echo "Medicinas sin vincular:        {$medSinLink->count()}\n";
echo "\n";
echo "Productos totales:             {$prods->count()}\n";
echo "  - Medicinas:                 " . $prods->where('tipo', 'medicina')->count() . "\n";
echo "  - Insumos:                   " . $prods->where('tipo', 'insumo')->count() . "\n";
echo "  - Con medicamento_id:        " . $prods->whereNotNull('medicamento_id')->count() . "\n";
echo "  - Sin medicamento_id:        " . $prods->whereNull('medicamento_id')->count() . "\n";
