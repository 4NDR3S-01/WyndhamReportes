<?php

namespace App\Services\Medico;

use App\Models\MedicoArchivoImportado;
use App\Models\MedicoCatalogo;
use App\Models\MedicoKardex;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoProducto;
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
            $partes = $this->cargarPartes($spreadsheet, $archivo);

            $archivo->update([
                'estado' => 'procesado',
                'filas_importadas' => $catalogos + $pacientes + $productos + $partes,
                'total_filas' => $catalogos + $pacientes + $productos + $partes,
                'fecha_procesado' => now(),
                'observaciones' => 'Carga inicial del panel medico desde Excel base.',
            ]);

            return compact('catalogos', 'pacientes', 'productos', 'partes');
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
        $creados = 0;

        // Process BOTH sheets — they contain different groups of employees
        foreach (['NOMINA', 'Copia de NOMINA'] as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if ($sheet) {
                $creados += $this->procesarHojaNomina($sheet);
            }
        }

        return $creados;
    }

    private function procesarHojaNomina($sheet): int
    {
        // Detect sheet variant: NOMINA has headers in row 2, Copia de NOMINA in row 1
        $esCopia = $sheet->getTitle() === 'Copia de NOMINA';
        $dataStartRow = $esCopia ? 2 : 3;

        // Column mapping for NOMINA (1-based):
        // A(1)=Nº  B(2)=Cédula  C(3)=Nombres  D(4)=AREA  E(5)=PUESTO  F(6)=fecha_ingreso
        // G(7)=PATOLOGIAS  H(8)=VACUNAS  I(9)=FICHAS ANTERIORES
        // J(10)=2021  K(11)=2022  L(12)=2023  M(13)=2024  N(14)=2025  O(15)=2026
        // P(16)=ESPIROMETRIA  Q(17)=empty  R(18)=ECOGRAFIA  S(19)=AUDIOMETRIA  T(20)=OPTOMETRIA
        // U(21)=Nº TELEFONICO  V(22)=antecedentes

        // Copia de NOMINA column mapping (different structure):
        // A(1)=Nº  B(2)=Cédula  C(3)=Nombres  D(4)=AREA  E(5)=PUESTO
        // F(6)=(vacunado data, no header)  G(7)=fecha_ingreso  H(8)=PATOLOGIAS  I(9)=VACUNAS
        // J(10)=FICHAS ANTERIORES  K(11)=2021  L(12)=2022  M(13)=2023  N(14)=2024
        // O(15)=ESPIROMETRIA  P(16)=ECOGRAFIA  Q(17)=AUDIOMETRIA  R(18)=OPTOMETRIA
        // S(19)=Nº TELEFONICO

        if ($esCopia) {
            $col = [
                'nombre' => 3, 'cedula' => 2, 'area' => 4, 'cargo' => 5,
                'fecha_ingreso' => 7, 'patologias' => 8, 'vacunas' => 9,
                'fichas_anteriores' => 10,
                'visitas' => [11 => 'visita_2021', 12 => 'visita_2022', 13 => 'visita_2023', 14 => 'visita_2024'],
                'espirometria' => 15, 'ecografia' => 16, 'audiometria' => 17, 'optometria' => 18,
                'telefono' => 19, 'antecedentes' => null,
            ];
        } else {
            $col = [
                'nombre' => 3, 'cedula' => 2, 'area' => 4, 'cargo' => 5,
                'fecha_ingreso' => 6, 'patologias' => 7, 'vacunas' => 8,
                'fichas_anteriores' => 9,
                'visitas' => [10 => 'visita_2021', 11 => 'visita_2022', 12 => 'visita_2023', 13 => 'visita_2024', 14 => 'visita_2025', 15 => 'visita_2026'],
                'espirometria' => 16, 'ecografia' => 18, 'audiometria' => 19, 'optometria' => 20,
                'telefono' => 21, 'antecedentes' => 22,
            ];
        }

        $creados = 0;
        for ($row = $dataStartRow; $row <= $sheet->getHighestRow(); $row++) {
            $nombre = $this->limpiarTexto($sheet->getCell([$col['nombre'], $row])->getValue());
            if ($nombre === '') continue;

            $cedula = $this->limpiarTexto($sheet->getCell([$col['cedula'], $row])->getValue());

            // Skip header-like rows (section labels like "SERVICIOS PRESTADOS", "ASPIRANTES")
            $nombreUpper = mb_strtoupper($nombre);
            if (in_array($nombreUpper, ['SERVICIOS PRESTADOS', 'ASPIRANTES', 'HUESPED', 'DOCTOR'], true)) {
                continue;
            }

            // Skip aspirante/huesped rows in Copia (no real employee data)
            $areaUpper = mb_strtoupper($this->limpiarTexto($sheet->getCell([$col['area'], $row])->getValue()) ?: '');
            if ($areaUpper === 'ASPIRANTES' && ($cedula === '' || $nombreUpper === 'ASPIRANTES')) {
                continue;
            }
            if ($nombreUpper === 'HUESPED' || $areaUpper === 'HUESPED') {
                continue;
            }
            if ($nombreUpper === 'DOCTOR') {
                continue;
            }

            $data = [
                'nombres'          => $nombre,
                'area'             => $this->limpiarTexto($sheet->getCell([$col['area'], $row])->getValue()) ?: null,
                'cargo'            => $this->limpiarTexto($sheet->getCell([$col['cargo'], $row])->getValue()) ?: null,
                'fecha_ingreso'    => $this->parsearFecha($sheet->getCell([$col['fecha_ingreso'], $row])->getValue()),
                'patologias'       => $this->limpiarTexto($sheet->getCell([$col['patologias'], $row])->getValue()) ?: null,
                'vacunas'          => $this->limpiarTexto($sheet->getCell([$col['vacunas'], $row])->getValue()) ?: null,
                'fichas_anteriores'=> $this->limpiarTexto($sheet->getCell([$col['fichas_anteriores'], $row])->getValue()) ?: null,
                'telefono'         => $this->limpiarTexto($sheet->getCell([$col['telefono'], $row])->getValue()) ?: null,
                'espirometria'     => $this->parsearFecha($sheet->getCell([$col['espirometria'], $row])->getValue()),
                'ecografia'        => $this->parsearFecha($sheet->getCell([$col['ecografia'], $row])->getValue()),
                'audiometria'      => $this->parsearFecha($sheet->getCell([$col['audiometria'], $row])->getValue()),
                'optometria'       => $this->parsearFecha($sheet->getCell([$col['optometria'], $row])->getValue()),
                'tipo'             => 'colaborador',
                'activo'           => true,
            ];

            // Yearly medical visits (2021-2026)
            foreach ($col['visitas'] as $colIdx => $dbField) {
                $fechaVisita = $this->parsearFecha($sheet->getCell([$colIdx, $row])->getValue());
                if ($fechaVisita) {
                    $data[$dbField] = $fechaVisita;
                }
            }

            // Antecedentes (only in NOMINA, not Copia)
            if ($col['antecedentes']) {
                $data['antecedentes'] = $this->limpiarTexto($sheet->getCell([$col['antecedentes'], $row])->getValue()) ?: null;
            }

            $paciente = MedicoPaciente::query()->updateOrCreate(
                $cedula !== '' ? ['cedula' => $cedula] : ['nombres' => $nombre],
                $data,
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
