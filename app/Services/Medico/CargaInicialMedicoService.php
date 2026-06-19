<?php

namespace App\Services\Medico;

use App\Models\MedicoArchivoImportado;
use App\Models\MedicoCatalogo;
use App\Models\MedicoKardex;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoProducto;
use App\Models\MedicoProductoAlias;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CargaInicialMedicoService
{
    public function __construct(private readonly InventarioMedicoService $inventario) {}

    public function cargar(string $ruta): array
    {
        if (! file_exists($ruta)) {
            throw new \RuntimeException('No se encontro el Excel base del departamento medico.');
        }

        $spreadsheet = IOFactory::load($ruta);

        return DB::transaction(function () use ($spreadsheet, $ruta): array {
            $archivo = $this->archivoInicial($ruta);

            $catalogos = $this->cargarCatalogos($spreadsheet);
            $pacientes = $this->cargarPacientesNomina($spreadsheet);
            $productos = $this->cargarProductosYKardex($spreadsheet, $archivo);
            $aliases = $this->crearAliasesBase();
            $partes = $this->cargarPartes($spreadsheet, $archivo);

            $archivo->update([
                'estado' => 'procesado',
                'filas_importadas' => $catalogos + $pacientes + $productos + $partes,
                'total_filas' => $catalogos + $pacientes + $productos + $partes,
                'fecha_procesado' => now(),
                'observaciones' => 'Carga inicial del panel medico desde Excel base.',
            ]);

            return compact('catalogos', 'pacientes', 'productos', 'partes', 'aliases');
        });
    }

    private function cargarCatalogos($spreadsheet): int
    {
        $sheet = $spreadsheet->getSheetByName('BASE DE DATOS');
        if (! $sheet) return 0;

        $mapa = [
            1 => 'area',
            2 => 'cargo',
            4 => 'subsidio',
            6 => 'causa',
            8 => 'medicacion',
            10 => 'certificado_medico',
            12 => 'descanso_en',
            14 => 'salida',
            16 => 'diagnostico',
            18 => 'cirugia_general',
            20 => 'flebologia_vascular',
            22 => 'atencion_medica_general',
            24 => 'incidente',
        ];

        $creados = 0;
        foreach ($mapa as $col => $tipo) {
            $orden = 1;
            for ($row = 4; $row <= $sheet->getHighestRow(); $row++) {
                $nombre = $this->limpiarTexto($sheet->getCell([$col, $row])->getValue());
                if ($nombre === '') continue;

                $item = MedicoCatalogo::query()->firstOrCreate(
                    ['tipo' => $tipo, 'nombre' => $nombre],
                    ['orden' => $orden, 'activo' => true],
                );
                if ($item->wasRecentlyCreated) $creados++;
                $orden++;
            }
        }

        return $creados;
    }

    private function cargarPacientesNomina($spreadsheet): int
    {
        $sheet = $spreadsheet->getSheetByName('NOMINA') ?: $spreadsheet->getSheetByName('Copia de NOMINA');
        if (! $sheet) return 0;

        $creados = 0;
        for ($row = 3; $row <= $sheet->getHighestRow(); $row++) {
            $nombre = $this->limpiarTexto($sheet->getCell([3, $row])->getValue());
            if ($nombre === '') continue;

            $cedula = $this->limpiarTexto($sheet->getCell([2, $row])->getValue());
            $paciente = MedicoPaciente::query()->updateOrCreate(
                $cedula !== '' ? ['cedula' => $cedula] : ['nombres' => $nombre],
                [
                    'nombres' => $nombre,
                    'area' => $this->limpiarTexto($sheet->getCell([4, $row])->getValue()) ?: null,
                    'cargo' => $this->limpiarTexto($sheet->getCell([5, $row])->getValue()) ?: null,
                    'fecha_ingreso' => $this->parsearFecha($sheet->getCell([6, $row])->getValue()),
                    'patologias' => $this->limpiarTexto($sheet->getCell([7, $row])->getValue()) ?: null,
                    'tipo' => 'colaborador',
                    'activo' => true,
                ],
            );

            if ($paciente->wasRecentlyCreated) $creados++;
        }

        return $creados;
    }

    private function cargarProductosYKardex($spreadsheet, MedicoArchivoImportado $archivo): int
    {
        $sheet = $spreadsheet->getSheetByName('KARDEX 2026');
        if (! $sheet) return 0;

        $periodo = $this->periodoKardex($sheet);
        $creados = 0;
        $seccion = null;

        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $primera = $this->limpiarTexto($sheet->getCell([1, $row])->getValue());
            if (mb_strtoupper($primera) === 'MEDICINAS') {
                $seccion = 'medicina';
                continue;
            }
            if (mb_strtoupper($primera) === 'EQUIPOS') {
                $seccion = 'equipo';
                continue;
            }
            if (! $seccion || $primera === '') continue;

            $producto = MedicoProducto::query()->firstOrCreate(
                ['nombre' => $primera],
                [
                    'tipo' => $seccion,
                    'fecha_caducidad' => $seccion === 'medicina' ? $this->parsearFecha($sheet->getCell([6, $row])->getValue()) : null,
                    'activo' => true,
                ],
            );
            if ($producto->wasRecentlyCreated) $creados++;

            $this->alias($producto, $primera);

            $saldoAnterior = $seccion === 'medicina' ? $this->parsearNumero($sheet->getCell([2, $row])->getCalculatedValue()) : 0;
            $ingresos = $seccion === 'medicina' ? $this->parsearNumero($sheet->getCell([3, $row])->getCalculatedValue()) : 0;
            $egresos = $seccion === 'medicina' ? $this->parsearNumero($sheet->getCell([4, $row])->getCalculatedValue()) : 0;
            $total = $seccion === 'medicina'
                ? $this->parsearNumero($sheet->getCell([5, $row])->getCalculatedValue())
                : $this->parsearNumero($sheet->getCell([2, $row])->getCalculatedValue());

            MedicoKardex::query()->firstOrCreate(
                [
                    'fecha_inicio' => $periodo['inicio'],
                    'fecha_fin' => $periodo['fin'],
                    'tipo' => $seccion,
                    'nombre' => $primera,
                ],
                [
                    'archivo_importado_id' => $archivo->id,
                    'saldo_anterior' => $saldoAnterior ?? 0,
                    'ingresos' => $ingresos ?? 0,
                    'egresos' => $egresos ?? 0,
                    'total' => $total ?? 0,
                    'fecha_caducidad' => $this->limpiarTexto($sheet->getCell([6, $row])->getFormattedValue()) ?: null,
                    'hash_unico' => MedicoKardex::generarHash($archivo->id, $row),
                ],
            );
        }

        $base = $spreadsheet->getSheetByName('BASE DE DATOS');
        if ($base) {
            for ($row = 4; $row <= $base->getHighestRow(); $row++) {
                $nombre = $this->limpiarTexto($base->getCell([8, $row])->getValue());
                if ($nombre === '') continue;
                $producto = MedicoProducto::query()->firstOrCreate(['nombre' => $nombre], ['tipo' => 'medicina', 'activo' => true]);
                $this->alias($producto, $nombre);
                if ($producto->wasRecentlyCreated) $creados++;
            }
        }

        return $creados;
    }

    private function cargarPartes($spreadsheet, MedicoArchivoImportado $archivo): int
    {
        $sheet = $spreadsheet->getSheetByName('PARTES DIARIO 2026');
        if (! $sheet) return 0;

        $creados = 0;
        for ($row = 7; $row <= $sheet->getHighestRow(); $row++) {
            $fecha = $this->parsearFecha($sheet->getCell([1, $row])->getValue());
            $nombres = $this->limpiarTexto($sheet->getCell([2, $row])->getValue());
            if (! $fecha || $nombres === '') continue;

            $area = $this->limpiarTexto($sheet->getCell([4, $row])->getValue()) ?: null;
            $cargo = $this->limpiarTexto($sheet->getCell([5, $row])->getValue()) ?: null;

            MedicoPaciente::query()->firstOrCreate(
                ['nombres' => $nombres],
                ['area' => $area, 'cargo' => $cargo, 'tipo' => 'paciente', 'activo' => true],
            );

            $parte = MedicoParteDiario::query()->firstOrCreate(
                [
                    'fecha' => $fecha,
                    'nombres' => $nombres,
                    'diagnostico' => $this->limpiarTexto($sheet->getCell([14, $row])->getValue()) ?: null,
                ],
                [
                    'archivo_importado_id' => $archivo->id,
                    'edad' => $this->parsearNumero($sheet->getCell([3, $row])->getValue()),
                    'area' => $area,
                    'cargo' => $cargo,
                    'certificados' => $this->limpiarTexto($sheet->getCell([6, $row])->getValue()) ?: null,
                    'subsidio' => $this->limpiarTexto($sheet->getCell([7, $row])->getValue()) ?: null,
                    'horas_certificado' => $this->parsearNumero($sheet->getCell([8, $row])->getValue()),
                    'dias_certificado' => $this->parsearNumero($sheet->getCell([9, $row])->getValue()),
                    'fecha_inicio_certificado' => $this->parsearFecha($sheet->getCell([10, $row])->getValue()),
                    'fecha_fin_certificado' => $this->parsearFecha($sheet->getCell([11, $row])->getValue()),
                    'medico_certifica' => $this->limpiarTexto($sheet->getCell([12, $row])->getValue()) ?: null,
                    'causa' => $this->limpiarTexto($sheet->getCell([13, $row])->getValue()) ?: null,
                    'medicamento_1' => $this->limpiarTexto($sheet->getCell([15, $row])->getValue()) ?: null,
                    'medicamento_2' => $this->limpiarTexto($sheet->getCell([16, $row])->getValue()) ?: null,
                    'medicamento_3' => $this->limpiarTexto($sheet->getCell([17, $row])->getValue()) ?: null,
                    'observacion' => $this->limpiarTexto($sheet->getCell([18, $row])->getValue()) ?: null,
                    'hash_unico' => MedicoParteDiario::generarHash($archivo->id, $row),
                ],
            );

            if ($parte->wasRecentlyCreated) {
                $creados++;
                $this->inventario->sincronizarMedicacionParte($parte, $this->medicamentosDesdeParte($parte));
            } elseif ($parte->medicamentos()->count() === 0) {
                $this->inventario->sincronizarMedicacionParte($parte, $this->medicamentosDesdeParte($parte));
            }
        }

        return $creados;
    }

    private function medicamentosDesdeParte(MedicoParteDiario $parte): array
    {
        return collect(['medicamento_1', 'medicamento_2', 'medicamento_3'])
            ->map(fn (string $campo) => ['nombre_original' => $parte->{$campo}, 'cantidad' => 1])
            ->filter(fn (array $m) => trim((string) $m['nombre_original']) !== '')
            ->values()
            ->all();
    }

    private function crearAliasesBase(): int
    {
        $aliases = [
            'Alercet' => ['ALERCET'],
            'Analgan' => ['Analgan tabletas 1g'],
            'tiras reactivas glucosa' => ['TIRILLAS ACCU CHEK'],
            'Jeringuillas 3 ML' => ['jeringuilla 3 cm'],
            'jeringuiilas 5cc' => ['jeringuilla 5 cm'],
            'Paracolic' => ['Paracolic IB comprimidos'],
            'Oralyte' => ['SUERO ORAL SOBRES'],
            'Diarem' => ['Diaren comprimidos 200/350mg'],
            'Dexametasona' => ['Dexametasona 8 mg. Amp.'],
            'Loratadina 10 mg' => ['Loratadina 10mg.tab'],
            'Esomeprazol 40 mg' => ['ESOMEPRAZOL DE 40 MG'],
            'ketorolaco 60 ml amp' => ['ketorolaco ampolla 60mg'],
            'Diclofenaco tabletas' => ['DICLOFENACO AMP'],
            'Buscapina duo' => ['Buscapina simple tabletas'],
            'Bactrim' => ['Bactrim forte'],
            'Decatileno' => ['Decatileno tabletas'],
            'Migradoxirina' => ['Migradorixina comprimido'],
            'Tensorelax forte' => ['TENSORELAX'],
            'Ibuprofeno 600mg' => ['Ibuprofeno tabletas 600mg'],
        ];

        $creados = 0;
        foreach ($aliases as $productoNombre => $aliasList) {
            $producto = MedicoProducto::query()->where('nombre', $productoNombre)->first();
            if (! $producto) continue;
            foreach ($aliasList as $alias) {
                if ($this->alias($producto, $alias)) $creados++;
            }
        }

        return $creados;
    }

    private function alias(MedicoProducto $producto, string $alias): bool
    {
        $normalizado = MedicoProducto::normalizarNombre($alias);
        $row = MedicoProductoAlias::query()->firstOrCreate(
            ['alias_normalizado' => $normalizado],
            ['producto_id' => $producto->id, 'alias' => $alias],
        );

        return $row->wasRecentlyCreated;
    }

    private function archivoInicial(string $ruta): MedicoArchivoImportado
    {
        $usuarioId = auth()->id() ?: User::query()->value('id');
        if (! $usuarioId) {
            throw new \RuntimeException('No existe usuario para asociar la carga inicial.');
        }

        return MedicoArchivoImportado::query()->firstOrCreate(
            ['nombre_guardado' => 'departamento-medico-inicial.xlsx'],
            [
                'usuario_id' => $usuarioId,
                'nombre_original' => basename($ruta),
                'ruta' => $ruta,
                'extension' => 'xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'tamano_bytes' => filesize($ruta) ?: 0,
                'estado' => 'recibido',
                'fecha_subida' => now(),
            ],
        );
    }

    private function periodoKardex($sheet): array
    {
        $texto = $this->limpiarTexto($sheet->getCell([1, 5])->getValue());
        if (preg_match('/del\s+(.+)\s+al\s+(.+)/i', $texto, $m)) {
            return [
                'inicio' => $this->parsearFechaTexto($m[1]) ?: now()->startOfMonth()->toDateString(),
                'fin' => $this->parsearFechaTexto($m[2]) ?: now()->endOfMonth()->toDateString(),
            ];
        }

        return ['inicio' => now()->startOfMonth()->toDateString(), 'fin' => now()->endOfMonth()->toDateString()];
    }

    private function parsearFechaTexto(string $texto): ?string
    {
        $meses = [
            'enero' => 'January', 'febrero' => 'February', 'marzo' => 'March', 'abril' => 'April',
            'mayo' => 'May', 'junio' => 'June', 'julio' => 'July', 'agosto' => 'August',
            'septiembre' => 'September', 'octubre' => 'October', 'noviembre' => 'November', 'diciembre' => 'December',
        ];
        $texto = mb_strtolower($texto);
        foreach ($meses as $es => $en) $texto = str_replace($es, $en, $texto);
        $texto = str_replace([' del ', ' de '], ' ', $texto);

        try {
            return Carbon::parse($texto)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parsearFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') return null;
        if ($valor instanceof \DateTimeInterface) return Carbon::instance($valor)->toDateString();
        if (is_numeric($valor)) return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');

        try {
            return Carbon::parse((string) $valor)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parsearNumero(mixed $valor): float|int|null
    {
        if ($valor === null || $valor === '') return null;
        if (is_numeric($valor)) return $valor + 0;
        $valor = str_replace(',', '.', trim((string) $valor));

        return is_numeric($valor) ? $valor + 0 : null;
    }

    private function limpiarTexto(mixed $valor): string
    {
        return trim((string) ($valor ?? ''));
    }
}
