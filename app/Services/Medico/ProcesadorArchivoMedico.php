<?php

namespace App\Services\Medico;

use App\Models\MedicoArchivoImportado;
use App\Models\MedicoImportacionError;
use App\Models\MedicoKardex;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoParteDiario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProcesadorArchivoMedico
{
    public function procesar(MedicoArchivoImportado $archivo): array
    {
        $rutaAbsoluta = Storage::path($archivo->ruta);

        if (! file_exists($rutaAbsoluta)) {
            throw new \RuntimeException('No se encontro el archivo fisico para procesar.');
        }

        $spreadsheet = IOFactory::load($rutaAbsoluta);
        $sheetNames = $spreadsheet->getSheetNames();

        $partesSheets = [];
        $kardexSheet = null;

        foreach ($sheetNames as $name) {
            $n = mb_strtoupper(trim($name));
            if (str_starts_with($n, 'PARTES DIARIO')) {
                $partesSheets[] = $name;
            }
            if (str_starts_with($n, 'KARDEX') && $kardexSheet === null) {
                $kardexSheet = $name;
            }
        }

        $partesTotal = ['importadas' => 0, 'errores' => 0, 'total' => 0, 'duplicadas' => 0, 'ids' => []];
        foreach ($partesSheets as $sheetName) {
            $result = $this->procesarPartesDiario($archivo, $spreadsheet, $sheetName);
            $partesTotal['importadas'] += $result['importadas'];
            $partesTotal['errores'] += $result['errores'];
            $partesTotal['total'] += $result['total'];
            $partesTotal['duplicadas'] += $result['duplicadas'];
            $partesTotal['ids'] = array_merge($partesTotal['ids'], $result['ids'] ?? []);
        }

        $kardex = $kardexSheet ? $this->procesarKardex($archivo, $spreadsheet, $kardexSheet) : ['importadas' => 0, 'errores' => 0, 'total' => 0, 'duplicadas' => 0];
        $movimientos = $this->descontarMedicamentos($archivo, $partesTotal['ids']);

        $archivo->update([
            'estado' => ($partesTotal['errores'] + $kardex['errores'] + $movimientos['errores']) > 0 ? 'procesado_con_errores' : 'procesado',
            'total_filas' => $partesTotal['total'] + $kardex['total'],
            'filas_importadas' => $partesTotal['importadas'] + $kardex['importadas'],
            'filas_con_error' => $partesTotal['errores'] + $kardex['errores'] + $movimientos['errores'],
            'fecha_procesado' => now(),
            'observaciones' => $this->formatearObservaciones($partesTotal, $kardex, $movimientos),
        ]);

        return [
            'importadas' => $partesTotal['importadas'] + $kardex['importadas'],
            'errores' => $partesTotal['errores'] + $kardex['errores'] + $movimientos['errores'],
            'total' => $partesTotal['total'] + $kardex['total'],
            'duplicadas' => $partesTotal['duplicadas'] + $kardex['duplicadas'],
            'partes_diarios' => $partesTotal,
            'kardex' => $kardex,
            'movimientos' => $movimientos,
        ];
    }

    private function procesarPartesDiario(MedicoArchivoImportado $archivo, $spreadsheet, string $sheetName): array
    {
        try {
            $sheet = $spreadsheet->getSheetByName($sheetName);
        } catch (\Throwable) {
            return ['importadas' => 0, 'errores' => 0, 'total' => 0, 'duplicadas' => 0, 'ids' => []];
        }

        $rows = $sheet->toArray(null, true, true, false);

        $headerRow = null;
        $dataStart = null;
        foreach ($rows as $i => $row) {
            $normalized = array_map(fn ($v) => mb_strtolower(trim((string) ($v ?? ''))), $row);
            if (in_array('fecha', $normalized) && in_array('nombres', $normalized)) {
                $headerRow = $row;
                $dataStart = $i + 1;
                break;
            }
        }

        if ($headerRow === null) {
            return ['importadas' => 0, 'errores' => 0, 'total' => 0, 'duplicadas' => 0, 'ids' => []];
        }

        return DB::transaction(function () use ($archivo, $rows, $headerRow, $dataStart) {
            MedicoImportacionError::where('archivo_importado_id', $archivo->id)->delete();

            $importadas = 0;
            $errores = 0;
            $duplicadas = 0;
            $totalFilas = 0;
            $ids = [];

            for ($i = $dataStart; $i < count($rows); $i++) {
                $row = $rows[$i];
                if ($this->filaVacia($row)) continue;

                $numeroFila = $i + 1;
                $totalFilas++;

                $datos = $this->normalizarParteDiario($row, $headerRow, $numeroFila);

                if ($datos['errores'] !== []) {
                    $errores++;
                    foreach ($datos['errores'] as $msg) {
                        MedicoImportacionError::create([
                            'archivo_importado_id' => $archivo->id,
                            'fila' => $numeroFila,
                            'mensaje' => $msg,
                        ]);
                    }
                    continue;
                }

                $d = $datos['datos'];
                $hash = MedicoParteDiario::generarHash($archivo->id, $numeroFila);

                if (MedicoParteDiario::where('hash_unico', $hash)->exists()) {
                    $duplicadas++;
                    continue;
                }

                $yaExiste = MedicoParteDiario::where('fecha', $d['fecha'])
                    ->where('nombres', $d['nombres'])
                    ->where('causa', $d['causa'])
                    ->where('diagnostico', $d['diagnostico'])
                    ->exists();

                if ($yaExiste) {
                    $duplicadas++;
                    continue;
                }

                $parte = MedicoParteDiario::create([
                    'archivo_importado_id' => $archivo->id,
                    'fecha' => $d['fecha'],
                    'nombres' => $d['nombres'],
                    'edad' => $d['edad'],
                    'area' => $d['area'],
                    'cargo' => $d['cargo'],
                    'certificados' => $d['certificados'],
                    'subsidio' => $d['subsidio'],
                    'horas_certificado' => $d['horas_certificado'],
                    'dias_certificado' => $d['dias_certificado'],
                    'fecha_inicio_certificado' => $d['fecha_inicio_certificado'],
                    'fecha_fin_certificado' => $d['fecha_fin_certificado'],
                    'medico_certifica' => $d['medico_certifica'],
                    'causa' => $d['causa'],
                    'diagnostico' => $d['diagnostico'],
                    'medicamento_1' => $d['medicamento_1'],
                    'medicamento_2' => $d['medicamento_2'],
                    'medicamento_3' => $d['medicamento_3'],
                    'observacion' => $d['observacion'],
                    'hash_unico' => $hash,
                ]);

                $ids[] = $parte->id;
                $importadas++;
            }

            return [
                'importadas' => $importadas,
                'errores' => $errores,
                'total' => $totalFilas,
                'duplicadas' => $duplicadas,
                'ids' => $ids,
            ];
        });
    }

    private function procesarKardex(MedicoArchivoImportado $archivo, $spreadsheet, string $sheetName): array
    {
        try {
            $sheet = $spreadsheet->getSheetByName($sheetName);
        } catch (\Throwable) {
            return ['importadas' => 0, 'errores' => 0, 'total' => 0, 'duplicadas' => 0];
        }

        $rows = $sheet->toArray(null, true, true, false);

        $periodo = $this->parsearPeriodoKardex($rows);

        return DB::transaction(function () use ($archivo, $rows, $periodo) {
            MedicoImportacionError::where('archivo_importado_id', $archivo->id)
                ->where('fila', '>=', 9000)
                ->delete();

            $importadas = 0;
            $errores = 0;
            $duplicadas = 0;
            $totalFilas = 0;
            $seccion = null;

            foreach ($rows as $i => $row) {
                if ($this->filaVacia($row)) continue;

                $firstVal = trim((string) ($row[0] ?? ''));
                $normalized = mb_strtolower($firstVal);

                if ($normalized === 'medicinas') {
                    $seccion = 'medicina';
                    continue;
                }
                if ($normalized === 'equipos') {
                    $seccion = 'equipo';
                    continue;
                }
                if ($seccion === null) continue;

                $numeroFila = $i + 1;
                $totalFilas++;

                $nombre = trim((string) ($row[0] ?? ''));
                if ($nombre === '' || $nombre === 'MEDICINAS' || $nombre === 'EQUIPOS') continue;

                if ($seccion === 'equipo') {
                    $saldoAnterior = 0;
                    $ingresos = 0;
                    $egresos = 0;
                    $total = $this->parsearNumero($row[1] ?? null) ?? 0;
                    $fechaCaducidad = null;
                } else {
                    $saldoAnterior = $this->parsearNumero($row[1] ?? null);
                    $ingresos = $this->parsearNumero($row[2] ?? null);
                    $egresos = $this->parsearNumero($row[3] ?? null);
                    $total = $this->parsearNumero($row[4] ?? null);
                    $fechaCaducidad = $this->limpiarTexto($row[5] ?? null);
                }

                $hash = MedicoKardex::generarHash($archivo->id, $numeroFila);

                if (MedicoKardex::where('hash_unico', $hash)->exists()) {
                    $duplicadas++;
                    continue;
                }

                if ($periodo) {
                    $yaExiste = MedicoKardex::where('fecha_inicio', $periodo['inicio'])
                        ->where('fecha_fin', $periodo['fin'])
                        ->where('tipo', $seccion)
                        ->where('nombre', $nombre)
                        ->exists();

                    if ($yaExiste) {
                        $duplicadas++;
                        continue;
                    }
                }

                MedicoKardex::create([
                    'archivo_importado_id' => $archivo->id,
                    'fecha_inicio' => $periodo['inicio'] ?? now()->startOfMonth(),
                    'fecha_fin' => $periodo['fin'] ?? now()->endOfMonth(),
                    'tipo' => $seccion,
                    'nombre' => $nombre,
                    'saldo_anterior' => $saldoAnterior ?? 0,
                    'ingresos' => $ingresos ?? 0,
                    'egresos' => $egresos ?? 0,
                    'total' => $total ?? 0,
                    'fecha_caducidad' => $fechaCaducidad,
                    'hash_unico' => $hash,
                ]);

                $importadas++;
            }

            return [
                'importadas' => $importadas,
                'errores' => $errores,
                'total' => $totalFilas,
                'duplicadas' => $duplicadas,
            ];
        });
    }

    private function descontarMedicamentos(MedicoArchivoImportado $archivo, array $parteDiarioIds): array
    {
        if ($parteDiarioIds === []) {
            return ['creados' => 0, 'errores' => 0, 'duplicados' => 0, 'sin_kardex' => 0];
        }

        return DB::transaction(function () use ($archivo, $parteDiarioIds) {
            $creados = 0;
            $errores = 0;
            $duplicados = 0;
            $sinKardex = 0;

            $partes = MedicoParteDiario::query()
                ->whereIn('id', $parteDiarioIds)
                ->orderBy('fecha')
                ->orderBy('id')
                ->get();

            foreach ($partes as $parte) {
                foreach (['medicamento_1', 'medicamento_2', 'medicamento_3'] as $campo) {
                    $nombre = $this->limpiarTexto($parte->{$campo});
                    if ($nombre === '') {
                        continue;
                    }

                    $kardex = MedicoKardex::query()
                        ->where('tipo', 'medicina')
                        ->where('nombre', $nombre)
                        ->orderByDesc('fecha_fin')
                        ->orderByDesc('id')
                        ->first();

                    if (! $kardex) {
                        $errores++;
                        $sinKardex++;

                        MedicoImportacionError::create([
                            'archivo_importado_id' => $archivo->id,
                            'fila' => $parte->id,
                            'columna' => $campo,
                            'valor' => $nombre,
                            'mensaje' => "Medicamento no encontrado en KARDEX: {$nombre}.",
                        ]);

                        continue;
                    }

                    $hash = MedicoKardexMovimiento::generarHash($parte->id, $campo, $nombre);

                    if (MedicoKardexMovimiento::where('hash_unico', $hash)->exists()) {
                        $duplicados++;
                        continue;
                    }

                    MedicoKardexMovimiento::create([
                        'kardex_id' => $kardex->id,
                        'parte_diario_id' => $parte->id,
                        'archivo_importado_id' => $archivo->id,
                        'medicamento_nombre' => $nombre,
                        'campo_medicamento' => $campo,
                        'cantidad' => 1,
                        'tipo' => 'salida',
                        'saldo_resultante' => $kardex->saldoActual() - 1,
                        'fecha_movimiento' => $parte->fecha,
                        'personal_responsable' => $parte->medico_certifica ?: ($archivo->usuario?->name ?? 'Sistema'),
                        'hash_unico' => $hash,
                    ]);

                    $creados++;
                }
            }

            return [
                'creados' => $creados,
                'errores' => $errores,
                'duplicados' => $duplicados,
                'sin_kardex' => $sinKardex,
            ];
        });
    }

    private function normalizarParteDiario(array $row, array $headers, int $numeroFila): array
    {
        $mapa = [];
        foreach ($headers as $i => $h) {
            $key = mb_strtolower(trim((string) ($h ?? '')));
            $key = preg_replace('/[^a-z0-9]/', '', $key) ?? '';
            $mapa[$key] = $i;
        }

        $fecha = $this->parsearFecha($this->valorPorClaves($row, $mapa, ['fecha']));
        $nombres = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['nombres']));
        $edad = $this->parsearNumero($this->valorPorClaves($row, $mapa, ['edad']));
        $area = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['area']));
        $cargo = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['cargo']));
        $certificados = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['certificados']));
        $subsidio = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['subsidio']));
        $horas = $this->parsearNumero($this->valorPorClaves($row, $mapa, ['horasdelcertificado', 'horascertificado']));
        $dias = $this->parsearNumero($this->valorPorClaves($row, $mapa, ['diasdecertificado', 'diascertificado']));
        $fechaInicio = $this->parsearFecha($this->valorPorClaves($row, $mapa, ['fechadeiniciodecertificado', 'fechainiciocertificado']));
        $fechaFin = $this->parsearFecha($this->valorPorClaves($row, $mapa, ['fechafindecertificado', 'fechafincertificado']));
        $medico = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['medicoquecertifica', 'medicocertifica']));
        $causa = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['causa']));
        $diagnostico = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['diagnostico']));
        $med1 = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['medicamento1']));
        $med2 = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['medicamento2']));
        $med3 = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['medicamento3']));
        $obs = $this->limpiarTexto($this->valorPorClaves($row, $mapa, ['observacion']));

        $errores = [];
        if (! $fecha) $errores[] = "Fila {$numeroFila}: fecha invalida o vacia.";
        if ($nombres === '') $errores[] = "Fila {$numeroFila}: nombre vacio.";

        return [
            'datos' => [
                'fecha' => $fecha,
                'nombres' => $nombres,
                'edad' => $edad,
                'area' => $area,
                'cargo' => $cargo,
                'certificados' => $certificados,
                'subsidio' => $subsidio,
                'horas_certificado' => $horas,
                'dias_certificado' => $dias,
                'fecha_inicio_certificado' => $fechaInicio,
                'fecha_fin_certificado' => $fechaFin,
                'medico_certifica' => $medico,
                'causa' => $causa,
                'diagnostico' => $diagnostico,
                'medicamento_1' => $med1,
                'medicamento_2' => $med2,
                'medicamento_3' => $med3,
                'observacion' => $obs,
            ],
            'errores' => $errores,
        ];
    }

    private function valorPorClaves(array $row, array $mapa, array $claves): mixed
    {
        foreach ($claves as $k) {
            if (isset($mapa[$k]) && isset($row[$mapa[$k]])) {
                return $row[$mapa[$k]];
            }
        }
        return null;
    }

    private function parsearPeriodoKardex(array $rows): ?array
    {
        foreach ($rows as $row) {
            $texto = trim((string) ($row[0] ?? ''));
            if (preg_match('/del\s+(\d{1,2}\s+\w+\s+del?\s+\d{4})\s+al\s+(\d{1,2}\s+\w+\s+del?\s+\d{4})/i', $texto, $m)) {
                try {
                    $inicio = Carbon::parse($this->normalizarFechaTexto($m[1]))->format('Y-m-d');
                    $fin = Carbon::parse($this->normalizarFechaTexto($m[2]))->format('Y-m-d');
                    return ['inicio' => $inicio, 'fin' => $fin];
                } catch (\Throwable) {}
            }
        }
        return null;
    }

    private function normalizarFechaTexto(string $texto): string
    {
        $meses = [
            'enero' => 'January', 'febrero' => 'February', 'marzo' => 'March',
            'abril' => 'April', 'mayo' => 'May', 'junio' => 'June',
            'julio' => 'July', 'agosto' => 'August', 'septiembre' => 'September',
            'octubre' => 'October', 'noviembre' => 'November', 'diciembre' => 'December',
        ];

        $texto = mb_strtolower(trim($texto));
        foreach ($meses as $es => $en) {
            $texto = str_replace($es, $en, $texto);
        }
        $texto = str_replace([' del ', ' de '], ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    private function parsearFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') return null;

        if (is_numeric($valor)) {
            return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
        }

        $valor = trim((string) $valor);
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $valor)->format('Y-m-d');
            } catch (\Throwable) {}
        }
        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parsearNumero(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') return null;
        if (is_numeric($valor)) return (float) $valor;

        $texto = trim((string) $valor);
        $texto = str_replace(' ', '', $texto);
        $texto = str_replace(',', '.', $texto);

        return is_numeric($texto) ? (float) $texto : null;
    }

    private function limpiarTexto(mixed $valor): string
    {
        return trim((string) ($valor ?? ''));
    }

    private function filaVacia(array $row): bool
    {
        return array_filter($row, fn ($v) => $v !== null && $v !== '') === [];
    }

    private function formatearObservaciones(array $partes, array $kardex, array $movimientos): string
    {
        $obs = [];
        $dup = $partes['duplicadas'] + $kardex['duplicadas'];
        if ($dup > 0) $obs[] = "Se omitieron {$dup} filas duplicadas.";
        if ($kardex['importadas'] > 0) $obs[] = "KARDEX: {$kardex['importadas']} registros.";
        if ($movimientos['creados'] > 0) $obs[] = "Salidas de medicamentos: {$movimientos['creados']} movimientos.";
        if ($movimientos['sin_kardex'] > 0) $obs[] = "Medicamentos sin KARDEX: {$movimientos['sin_kardex']}.";
        return implode(' ', $obs) ?: null;
    }
}
