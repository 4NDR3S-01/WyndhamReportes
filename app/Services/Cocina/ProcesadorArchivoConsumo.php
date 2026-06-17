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

            foreach ($filas as $indice => $fila) {
                $numeroFila = $indice + 2;
                $resultado = $this->normalizarFila($fila, $encabezados, $numeroFila);

                if ($resultado['errores'] !== []) {
                    $errores++;
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
                $hash = CocinaConsumo::generarHash($datos['fecha'], $producto->id, $datos['servicio'], $datos['concepto']);

                if (CocinaConsumo::query()->where('hash_unico', $hash)->exists()) {
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

            return [
                'importadas' => $importadas,
                'errores' => $errores,
                'total' => count($filas),
                'duplicadas' => $duplicadas,
            ];
        });
    }

    /** @return array{0: array<int, string>, 1: array<int, array<int, mixed>>} */
    private function leerArchivo(string $rutaAbsoluta): array
    {
        $spreadsheet = IOFactory::load($rutaAbsoluta);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
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

    /** @return array{datos: array<string, mixed>, errores: array<int, string>} */
    private function normalizarFila(array $fila, array $encabezados, int $numeroFila): array
    {
        $mapa = [];

        foreach ($encabezados as $indice => $encabezado) {
            $mapa[$this->normalizarEncabezado($encabezado)] = $fila[$indice] ?? null;
        }

        $fecha = $this->parsearFecha($this->valor($mapa, ['fecha']));
        $codigo = trim((string) $this->valor($mapa, ['codigoarticulo', 'codigo', 'codarticulo']));
        $producto = trim((string) $this->valor($mapa, ['nombrearticulo', 'articulo', 'producto']));
        $unidad = trim((string) $this->valor($mapa, ['presentacion', 'unidad', 'unidadmedida']));
        $concepto = trim((string) $this->valor($mapa, ['nombreconcepto', 'concepto']));
        $grupo = trim((string) $this->valor($mapa, ['nombregrupo', 'grupo', 'categoria']));
        $cantidad = $this->parsearNumero($this->valor($mapa, ['cantidad']));
        $valor = $this->parsearNumero($this->valor($mapa, ['valor']), true);
        $errores = [];

        if (! $fecha) {
            $errores[] = "Fila {$numeroFila}: fecha invalida.";
        }

        if ($producto === '') {
            $errores[] = "Fila {$numeroFila}: producto vacio.";
        }

        if ($unidad === '') {
            $errores[] = "Fila {$numeroFila}: presentacion/unidad vacia.";
        }

        if ($cantidad === null || $cantidad < 0) {
            $errores[] = "Fila {$numeroFila}: cantidad invalida.";
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
        ];
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

        if (is_numeric($valor)) {
            return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $valor))->format('Y-m-d');
            } catch (\Throwable) {
                // Sigue intentando otros formatos frecuentes.
            }
        }

        try {
            return Carbon::parse((string) $valor)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
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
