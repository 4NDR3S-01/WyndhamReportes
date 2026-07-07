<?php

namespace Database\Seeders;

use App\Models\Medicamento;
use App\Models\MedicoKardex;
use App\Models\MedicoProducto;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MedicoKardexSeeder extends Seeder
{
    private array $medicamentosMap = [];

    public function run(): void
    {
        $ruta = base_path('assets/DEPARTAMENTO_MEDICO.xlsx');

        if (! file_exists($ruta)) {
            $this->command?->error("No se encontró el Excel: {$ruta}");
            return;
        }

        $this->medicamentosMap = Medicamento::query()->pluck('id', 'nombre')->all();

        $spreadsheet = IOFactory::load($ruta);
        $sheet = $spreadsheet->getSheetByName('KARDEX 2026');

        if (! $sheet) {
            $this->command?->warn('Hoja KARDEX 2026 no encontrada.');
            return;
        }

        // Fechas del período (fila 7, columnas H e I)
        $fechaInicioObj = $this->parsearFecha($sheet->getCell('H7')->getValue());
        $fechaFinObj    = $this->parsearFecha($sheet->getCell('I7')->getValue());
        $fechaInicio = $fechaInicioObj?->format('Y-m-d') ?? '2026-04-30';
        $fechaFin    = $fechaFinObj?->format('Y-m-d') ?? '2026-05-29';

        $this->command?->info("=== Importando KARDEX 2026 ({$fechaInicio} — {$fechaFin}) ===");

        // ── MEDICINAS (filas 7–63) ──
        $this->command?->info('--- MEDICINAS ---');
        $kardexOk = 0;
        $kardexUp = 0;

        for ($r = 7; $r <= 63; $r++) {
            $nombre = trim((string) ($sheet->getCell('A' . $r)->getValue() ?? ''));
            if ($nombre === '') continue;

            $saldoAnterior = $this->parsearFloat($sheet->getCell('B' . $r)->getValue()) ?? 0;
            $ingresos      = $this->parsearFloat($sheet->getCell('C' . $r)->getValue()) ?? 0;
            $egresos       = $this->parsearFloat($sheet->getCell('D' . $r)->getValue()) ?? 0;
            $total         = $saldoAnterior + $ingresos - $egresos;
            $fechaCad      = $this->parsearFecha($sheet->getCell('F' . $r)->getValue());

            // Producto: buscar existente o crear
            $producto = MedicoProducto::resolverPorNombre($nombre)
                ?? MedicoProducto::query()->create([
                    'tipo'   => 'medicina',
                    'nombre' => $this->capitalizar($nombre),
                    'activo' => true,
                ]);

            // Vincular con medicamento del catálogo
            if (! $producto->medicamento_id) {
                $producto->update(['medicamento_id' => $this->buscarMedicamento($nombre)]);
            }

            // Fecha de caducidad al producto
            if ($fechaCad && ! $producto->fecha_caducidad) {
                $producto->update(['fecha_caducidad' => $fechaCad->format('Y-m-d')]);
            }

            // Crear o actualizar registro kardex
            $kardex = MedicoKardex::query()->updateOrCreate(
                [
                    'nombre'       => $producto->nombre,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin'    => $fechaFin,
                ],
                [
                    'tipo'            => $producto->tipo,
                    'saldo_anterior'  => $saldoAnterior,
                    'ingresos'        => $ingresos,
                    'egresos'         => $egresos,
                    'total'           => $total,
                    'fecha_caducidad' => $fechaCad?->format('Y-m-d'),
                    'hash_unico'      => hash('sha256', implode('|', ['kardex2026',
                        $producto->id, $fechaInicio, $fechaFin])),
                ]
            );

            $kardex->wasRecentlyCreated ? $kardexOk++ : $kardexUp++;
        }

        $this->command?->info("  Kardex medicinas: {$kardexOk} nuevos, {$kardexUp} actualizados");

        // ── INSUMOS (filas 67–81) ──
        $this->command?->info('--- INSUMOS ---');
        $eqOk = 0;
        $eqUp = 0;

        for ($r = 67; $r <= 81; $r++) {
            $nombre = trim((string) ($sheet->getCell('A' . $r)->getValue() ?? ''));
            if ($nombre === '') continue;

            $cantidad = $this->parsearFloat($sheet->getCell('B' . $r)->getValue()) ?? 0;
            $stock    = $this->parsearFloat($sheet->getCell('C' . $r)->getValue()) ?? $cantidad;

            $producto = MedicoProducto::resolverPorNombre($nombre)
                ?? MedicoProducto::query()->create([
                    'tipo'   => 'insumo',
                    'nombre' => $this->capitalizar($nombre),
                    'activo' => true,
                ]);

            $kardex = MedicoKardex::query()->updateOrCreate(
                [
                    'nombre'       => $producto->nombre,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin'    => $fechaFin,
                ],
                [
                    'tipo'           => 'insumo',
                    'saldo_anterior' => $stock,
                    'ingresos'       => 0,
                    'egresos'        => 0,
                    'total'          => $stock,
                    'hash_unico'     => hash('sha256', implode('|', ['kardex2026eq',
                        $producto->id, $fechaInicio, $fechaFin])),
                ]
            );

            $kardex->wasRecentlyCreated ? $eqOk++ : $eqUp++;
        }

        $this->command?->info("  Kardex insumos: {$eqOk} nuevos, {$eqUp} actualizados");

        // ── Resumen final ──
        $totalProd = MedicoProducto::query()->count();
        $conKardex = MedicoKardex::query()->distinct('nombre')->count('nombre');
        $this->command?->info("Total productos en inventario: {$totalProd}");
        $this->command?->info("Productos con kardex: {$conKardex}");
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    private function buscarMedicamento(string $nombre): ?int
    {
        $n = $this->normalizar($nombre);
        if (isset($this->medicamentosMap[$n])) return $this->medicamentosMap[$n];

        foreach ($this->medicamentosMap as $cat => $id) {
            if (str_contains($cat, $n) || str_contains($n, $cat)) return $id;
        }

        $best = PHP_INT_MAX; $bestId = null;
        foreach ($this->medicamentosMap as $cat => $id) {
            $d = levenshtein($n, $cat);
            $m = max(mb_strlen($n), mb_strlen($cat));
            if ($m > 3 && $d <= ceil($m * 0.25) && $d < $best) { $best = $d; $bestId = $id; }
        }
        return $bestId;
    }

    private function normalizar(string $v): string
    {
        $v = mb_strtoupper(trim($v), 'UTF-8');
        $v = strtr($v, ['Á'=>'A','À'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I','Ó'=>'O','Ò'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','Ç'=>'C','Ñ'=>'N']);
        $v = preg_replace('/[\-\/\|\\\\]+/', ' ', $v);
        $v = preg_replace('/[,.!;:¿?¡"\'#@&*()\[\]{}<>]/u', '', $v);
        return trim(preg_replace('/\s+/', ' ', $v));
    }

    private function capitalizar(string $n): string
    {
        return mb_convert_case(mb_strtolower(trim($n), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function parsearFecha(mixed $v): ?\DateTimeInterface
    {
        if ($v === null || $v === '') return null;
        try {
            if (is_numeric($v) && $v > 30000 && $v < 80000) return Date::excelToDateTimeObject((float) $v);
            $ts = strtotime((string) $v);
            if ($ts !== false && $ts > 0) return new \DateTime(date('Y-m-d', $ts));
        } catch (\Throwable) {}
        return null;
    }

    private function parsearFloat(mixed $v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float) $v;
        $v = str_replace(',', '.', (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }
}
