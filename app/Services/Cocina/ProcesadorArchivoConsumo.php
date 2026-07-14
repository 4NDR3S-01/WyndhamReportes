<?php

namespace App\Services\Cocina;

use App\Models\CocinaArchivoImportado;
use App\Models\CocinaConsumo;
use App\Models\CocinaImportacionError;
use App\Models\CocinaProducto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProcesadorArchivoConsumo
{
    /**
     * @return array{importadas: int, errores: int, total: int, duplicadas: int}
     */
    public function procesar(CocinaArchivoImportado $archivo): array
    {
        $rutaAbsoluta = Storage::path($archivo->ruta);

        if (! file_exists($rutaAbsoluta)) {
            throw new \RuntimeException('No se encontro el archivo fisico para procesar.');
        }

        [$encabezados, $filas] = $this->leerArchivo($rutaAbsoluta);

        return DB::transaction(function () use ($archivo, $encabezados, $filas): array {
            CocinaImportacionError::query()
                ->where('archivo_importado_id', $archivo->id)
                ->delete();

            $importadas = 0;
            $errores = 0;
            $duplicadas = 0;
            $ultimaFecha = null;
            $motivos = [];

            foreach ($filas as $indice => $fila) {
                $numeroFila = $indice + 2;
                $resultado = $this->normalizarFila($fila, $encabezados, $numeroFila, $ultimaFecha);

                // Arrastra la ultima fecha valida hacia las filas siguientes del mismo
                // grupo/dia (comun en reportes donde la fecha se muestra una sola vez).
                if ($resultado['datos']['fecha'] !== null) {
                    $ultimaFecha = $resultado['datos']['fecha'];
                }

                if ($resultado['errores'] !== []) {
                    $errores++;
                    foreach ($resultado['motivos'] as $motivo) {
                        $motivos[$motivo] = ($motivos[$motivo] ?? 0) + 1;
                    }
                    foreach ($resultado['errores'] as $mensaje) {
                        CocinaImportacionError::query()->create([
                            'archivo_importado_id' => $archivo->id,
                            'fila' => $numeroFila,
                            'mensaje' => $mensaje,
                        ]);
                    }

                    continue;
                }

                $datos = $resultado['datos'];
                $producto = $this->resolverProducto($datos);

                $hash = CocinaConsumo::generarHash($archivo->id, $numeroFila);

                if (CocinaConsumo::query()->where('hash_unico', $hash)->exists()) {
                    $duplicadas++;
                    continue;
                }

                $yaExiste = CocinaConsumo::query()
                    ->where('fecha', $datos['fecha'])
                    ->where('producto_id', $producto->id)
                    ->where('servicio', $datos['servicio'])
                    ->where('concepto', $datos['concepto'])
                    ->exists();

                if ($yaExiste) {
                    $duplicadas++;
                    continue;
                }

                CocinaConsumo::query()->create([
                    'archivo_importado_id' => $archivo->id,
                    'producto_id' => $producto->id,
                    'fecha' => $datos['fecha'],
                    'servicio' => $datos['servicio'],
                    'concepto' => $datos['concepto'],
                    'unidad_medida' => $datos['unidad_medida'],
                    'cantidad' => $datos['cantidad'],
                    'valor' => $datos['valor'],
                    'hash_unico' => $hash,
                ]);

                $importadas++;
            }

            $archivo->update([
                'estado' => $errores > 0 ? 'procesado_con_errores' : 'procesado',
                'total_filas' => count($filas),
                'filas_importadas' => $importadas,
                'filas_con_error' => $errores,
                'fecha_procesado' => now(),
                'observaciones' => $duplicadas > 0 ? "Se omitieron {$duplicadas} filas duplicadas." : null,
            ]);

            $resumen = '';
            if ($motivos !== []) {
                $partes = [];
                foreach ($motivos as $motivo => $cantidad) {
                    $partes[] = "{$cantidad} {$motivo}";
                }
                $resumen = ' (' . implode(', ', $partes) . ')';
            }

            return [
                'importadas' => $importadas,
                'errores' => $errores,
                'total' => count($filas),
                'duplicadas' => $duplicadas,
                'resumen' => $resumen,
            ];
        });
    }

    /** @return array{0: array<int, string>, 1: array<int, array<int, mixed>>} */
    private function leerArchivo(string $rutaAbsoluta): array
    {
        $spreadsheet = IOFactory::load($rutaAbsoluta);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, false, false);
        $rows = array_values(array_filter($rows, fn (array $row): bool => array_filter($row, fn ($value): bool => $value !== null && $value !== '') !== []));

        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($value): string => $this->normalizarEncabezado((string) $value), $row);

            if (in_array('fecha', $normalized, true) && in_array('cantidad', $normalized, true)) {
                $headers = array_map(fn ($value): string => trim((string) $value), $row);

                return [$headers, array_slice($rows, $index + 1)];
            }
        }

        throw new \RuntimeException('No se encontro una fila de encabezados con Fecha y Cantidad.');
    }

    /** @return array{datos: array<string, mixed>, errores: array<int, string>, motivos: array<int, string>} */
    private function normalizarFila(array $fila, array $encabezados, int $numeroFila, ?string &$ultimaFecha): array
    {
        $mapa = [];

        foreach ($encabezados as $indice => $encabezado) {
            $mapa[$this->normalizarEncabezado($encabezado)] = $fila[$indice] ?? null;
        }

        $fechaCruda = $this->valor($mapa, ['fecha']);
        $fecha = $this->parsearFecha($fechaCruda);

        // Forward-fill: si la fila no trae fecha pero es del mismo grupo/dia
        // (reporte con fecha combinada), se hereda la ultima fecha valida.
        if ($fecha === null && $ultimaFecha !== null && trim((string) $fechaCruda) === '') {
            $fecha = $ultimaFecha;
        }

        $codigo = trim((string) $this->valor($mapa, ['codigoarticulo', 'codigo', 'codarticulo']));
        $producto = trim((string) $this->valor($mapa, ['nombrearticulo', 'articulo', 'producto']));
        $unidad = trim((string) $this->valor($mapa, ['presentacion', 'unidad', 'unidadmedida']));
        $concepto = trim((string) $this->valor($mapa, ['nombreconcepto', 'concepto']));
        $grupo = trim((string) $this->valor($mapa, ['nombregrupo', 'grupo', 'categoria']));
        $cantidad = $this->parsearNumero($this->valor($mapa, ['cantidad']));
        $valor = $this->parsearNumero($this->valor($mapa, ['valor']), true);
        $errores = [];
        $motivos = [];

        if (! $fecha) {
            $errores[] = "Fila {$numeroFila}: fecha invalida (valor: " . $this->resumir($fechaCruda) . ').';
            $motivos[] = 'fecha invalida';
        }

        if ($producto === '') {
            $errores[] = "Fila {$numeroFila}: producto vacio.";
            $motivos[] = 'producto vacio';
        }

        if ($unidad === '') {
            $errores[] = "Fila {$numeroFila}: presentacion/unidad vacia.";
            $motivos[] = 'unidad vacia';
        }

        if ($cantidad === null || $cantidad < 0) {
            $errores[] = "Fila {$numeroFila}: cantidad invalida (valor: " . $this->resumir($this->valor($mapa, ['cantidad'])) . ').';
            $motivos[] = 'cantidad invalida';
        }

        return [
            'datos' => [
                'fecha' => $fecha,
                'codigo' => $codigo ?: null,
                'producto' => $producto,
                'unidad_medida' => $unidad,
                'concepto' => $concepto ?: 'Consumo de cocina',
                'grupo' => $grupo ?: null,
                'cantidad' => $cantidad,
                'valor' => $valor,
                'servicio' => $this->inferirServicio($concepto),
            ],
            'errores' => $errores,
            'motivos' => $motivos,
        ];
    }

    private function resumir(mixed $valor): string
    {
        if ($valor === null) {
            return 'null';
        }

        $texto = (string) $valor;
        $texto = str_replace(["\n", "\r", "\t"], ' ', $texto);

        return mb_strlen($texto) > 40 ? mb_substr($texto, 0, 40) . '…' : $texto;
    }

    private function resolverProducto(array $datos): CocinaProducto
    {
        $query = CocinaProducto::query();

        if ($datos['codigo']) {
            $query->where('codigo', $datos['codigo']);
        } else {
            $query->where('nombre', $datos['producto']);
        }

        $producto = $query->first();

        if ($producto) {
            $producto->update([
                'nombre' => $datos['producto'],
                'unidad_medida' => $datos['unidad_medida'],
                'grupo' => $datos['grupo'],
            ]);

            return $producto;
        }

        return CocinaProducto::query()->create([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['producto'],
            'unidad_medida' => $datos['unidad_medida'],
            'grupo' => $datos['grupo'],
            'activo' => true,
        ]);
    }

    private function normalizarEncabezado(string $valor): string
    {
        $valor = Str::ascii(Str::lower(trim($valor)));

        return preg_replace('/[^a-z0-9]/', '', $valor) ?? '';
    }

    private function valor(array $mapa, array $llaves): mixed
    {
        foreach ($llaves as $llave) {
            if (array_key_exists($llave, $mapa)) {
                return $mapa[$llave];
            }
        }

        return null;
    }

    private function parsearFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $limiteInferior = 2000;
        $limiteSuperior = now()->year;

        // Numero serial de Excel (fecha almacenada como numero)
        if (is_numeric($valor)) {
            try {
                $fecha = ExcelDate::excelToDateTimeObject((float) $valor);
            } catch (\Throwable) {
                return null;
            }

            // Solo corrige el desfase del sistema 1900 vs 1904 (anos cercanos a 1900).
            $year = (int) $fecha->format('Y');
            if ($year >= 1900 && $year < 2000) {
                $ajustada = (clone $fecha)->modify('+1462 days');
                if ((int) $ajustada->format('Y') >= $limiteInferior && (int) $ajustada->format('Y') <= $limiteSuperior) {
                    $fecha = $ajustada;
                }
            }

            $year = (int) $fecha->format('Y');
            if ($year < $limiteInferior || $year > $limiteSuperior) {
                return null;
            }

            return $fecha->format('Y-m-d');
        }

        $texto = trim((string) $valor);

        // Un numero suelto (ano o dia aislado, p. ej. "2027") no es una fecha valida.
        if (preg_match('/^\d{1,4}$/', $texto)) {
            return null;
        }

        // El Excel usa de forma consistente dia/mes/ano. Se prueba ESE orden primero
        // (incluyendo variantes sin cero inicial y año de 2 dígitos),
        // y el formato mes/dia/ano (US) solo como respaldo, para no invertir fechas
        // ambiguas (dia <= 12) que el usuario reporto como "salen mes/dia/ano".
        //
        // NOTA: las celdas de fecha real de Excel ya llegan como número serial
        // (formatData=false en toArray) y las resuelve ExcelDate::excelToDateTimeObject;
        // este loop solo aplica a celdas de texto.
        foreach (['d/m/Y', 'd-m-Y', 'd/m/y', 'd-m-y', 'j/n/Y', 'j-n-Y', 'j/n/y', 'j-n-y', 'm/d/Y', 'm-d-Y', 'Y-m-d', 'Y/m/d'] as $format) {
            try {
                $fecha = Carbon::createFromFormat($format, $texto);
                if ($fecha !== false && $fecha->year >= $limiteInferior && $fecha->year <= $limiteSuperior) {
                    return $fecha->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Sigue intentando otros formatos frecuentes.
            }
        }

        return null;
    }

    private function parsearNumero(mixed $valor, bool $permitirNegativo = false): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            $numero = (float) $valor;

            return $numero < 0 && ! $permitirNegativo ? null : $numero;
        }

        $texto = trim((string) $valor);
        $texto = str_replace(' ', '', $texto);

        if (str_contains($texto, ',') && str_contains($texto, '.')) {
            $texto = str_replace('.', '', $texto);
        }

        $texto = str_replace(',', '.', $texto);

        if (! is_numeric($texto)) {
            return null;
        }

        $numero = (float) $texto;

        return $numero < 0 && ! $permitirNegativo ? null : $numero;
    }

    private function inferirServicio(string $concepto): string
    {
        $texto = Str::lower(Str::ascii($concepto));

        return match (true) {
            str_contains($texto, 'desayuno') => 'desayuno',
            str_contains($texto, 'almuerzo') => 'almuerzo',
            str_contains($texto, 'cena') => 'cena',
            default => 'otro',
        };
    }
}
