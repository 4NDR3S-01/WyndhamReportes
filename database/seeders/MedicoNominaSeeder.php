<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\MedicoPaciente;
use App\Models\MedicoPacienteExamen;
use App\Models\MedicoPacienteVisita;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MedicoNominaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Importando nómina médica ===');

        $file = base_path('assets/DEPARTAMENTO_MEDICO.xlsx');
        $spreadsheet = IOFactory::load($file);

        // === FASE 1: Leer Copia de NOMINA (más datos: teléfono, exámenes) ===
        $this->command->info('Leyendo Copia de NOMINA...');
        $copia = $spreadsheet->getSheetByName('Copia de NOMINA');
        $maxRow = $copia->getHighestDataRow();

        $pacientes = []; // keyed by cedula

        for ($r = 2; $r <= $maxRow; $r++) {
            $cedula = $this->normalizarCedula($copia->getCell('B' . $r)->getValue());
            $nombre = $this->normalizar($copia->getCell('C' . $r)->getValue());

            if ($cedula === '' || $nombre === '') {
                continue;
            }

            $pacientes[$cedula] = [
                'cedula'            => $cedula,
                'nombres'           => $nombre,
                'area_nombre'       => $this->normalizar($copia->getCell('D' . $r)->getValue()),
                'cargo_nombre'      => $this->normalizar($copia->getCell('E' . $r)->getValue()),
                'fecha_ingreso'     => $this->excelDate($copia->getCell('G' . $r)->getValue()),
                'patologias'        => $this->normalizarTexto($copia->getCell('H' . $r)->getValue()),
                'vacunas'           => $this->normalizarTexto($copia->getCell('I' . $r)->getValue()),
                'fichas_anteriores' => $this->normalizarTexto($copia->getCell('J' . $r)->getValue()),
                'telefono'          => $this->normalizarTelefono($copia->getCell('S' . $r)->getValue()),
                // Exámenes
                'examenes' => [
                    'espirometria' => $this->excelDate($copia->getCell('O' . $r)->getValue()),
                    'ecografia'    => $this->excelDate($copia->getCell('P' . $r)->getValue()),
                    'audiometria'  => $this->excelDate($copia->getCell('Q' . $r)->getValue()),
                    'optometria'   => $this->excelDate($copia->getCell('R' . $r)->getValue()),
                ],
                // Visitas anuales (Copia tiene 2021-2024 en K-N)
                'visitas' => [],
            ];

            // Visitas 2021-2024
            $yearCols = ['K' => 2021, 'L' => 2022, 'M' => 2023, 'N' => 2024];
            foreach ($yearCols as $col => $year) {
                $fecha = $this->excelDate($copia->getCell($col . $r)->getValue());
                if ($fecha) {
                    $pacientes[$cedula]['visitas'][$year] = $fecha;
                }
            }
        }
        $this->command->info('  Copia de NOMINA: ' . count($pacientes) . ' pacientes únicos');

        // === FASE 2: Leer NOMINA (visitas 2021-2026, algunas columnas distintas) ===
        $this->command->info('Leyendo NOMINA...');
        $nomina = $spreadsheet->getSheetByName('NOMINA');
        $maxRow = $nomina->getHighestDataRow();

        $nominaCount = 0;
        for ($r = 3; $r <= $maxRow; $r++) {
            $cedula = $this->normalizarCedula($nomina->getCell('B' . $r)->getValue());
            $nombre = $this->normalizar($nomina->getCell('C' . $r)->getValue());

            if ($cedula === '' || $nombre === '') {
                continue;
            }

            $nominaCount++;

            // Si ya existe de Copia, solo agregamos datos faltantes
            if (isset($pacientes[$cedula])) {
                $p = &$pacientes[$cedula];

                // Visitas 2021-2026 de NOMINA (columnas J-O)
                $yearCols = ['J' => 2021, 'K' => 2022, 'L' => 2023, 'M' => 2024, 'N' => 2025, 'O' => 2026];
                foreach ($yearCols as $col => $year) {
                    if (! isset($p['visitas'][$year])) {
                        $fecha = $this->excelDate($nomina->getCell($col . $r)->getValue());
                        if ($fecha) {
                            $p['visitas'][$year] = $fecha;
                        }
                    }
                }

                // Completar vacíos de NOMINA si Copia no los tiene
                if (empty($p['patologias'])) {
                    $p['patologias'] = $this->normalizarTexto($nomina->getCell('G' . $r)->getValue());
                }
                if (empty($p['vacunas'])) {
                    $p['vacunas'] = $this->normalizarTexto($nomina->getCell('H' . $r)->getValue());
                }
                if (empty($p['fichas_anteriores'])) {
                    $p['fichas_anteriores'] = $this->normalizarTexto($nomina->getCell('I' . $r)->getValue());
                }
                if (empty($p['fecha_ingreso'])) {
                    $p['fecha_ingreso'] = $this->excelDate($nomina->getCell('F' . $r)->getValue());
                }
                if (empty($p['area_nombre'])) {
                    $p['area_nombre'] = $this->normalizar($nomina->getCell('D' . $r)->getValue());
                }
                if (empty($p['cargo_nombre'])) {
                    $p['cargo_nombre'] = $this->normalizar($nomina->getCell('E' . $r)->getValue());
                }
            } else {
                // Nuevo paciente solo de NOMINA
                $pacientes[$cedula] = [
                    'cedula'            => $cedula,
                    'nombres'           => $nombre,
                    'area_nombre'       => $this->normalizar($nomina->getCell('D' . $r)->getValue()),
                    'cargo_nombre'      => $this->normalizar($nomina->getCell('E' . $r)->getValue()),
                    'fecha_ingreso'     => $this->excelDate($nomina->getCell('F' . $r)->getValue()),
                    'patologias'        => $this->normalizarTexto($nomina->getCell('G' . $r)->getValue()),
                    'vacunas'           => $this->normalizarTexto($nomina->getCell('H' . $r)->getValue()),
                    'fichas_anteriores' => $this->normalizarTexto($nomina->getCell('I' . $r)->getValue()),
                    'telefono'          => null,
                    'examenes'          => [
                        'espirometria' => null,
                        'ecografia'    => null,
                        'audiometria'  => null,
                        'optometria'   => null,
                    ],
                    'visitas' => [],
                ];

                // Visitas 2021-2026
                $yearCols = ['J' => 2021, 'K' => 2022, 'L' => 2023, 'M' => 2024, 'N' => 2025, 'O' => 2026];
                foreach ($yearCols as $col => $year) {
                    $fecha = $this->excelDate($nomina->getCell($col . $r)->getValue());
                    if ($fecha) {
                        $pacientes[$cedula]['visitas'][$year] = $fecha;
                    }
                }
            }
        }
        $this->command->info('  NOMINA: ' . $nominaCount . ' filas leídas');
        $this->command->info('  Total pacientes combinados: ' . count($pacientes));

        // === FASE 3: Pre-cache de áreas y cargos normalizados ===
        $this->command->info('Pre-cache de catálogos...');
        $areasMap = Area::query()->pluck('id', 'nombre')->all();
        $cargosMap = Cargo::query()->pluck('id', 'nombre')->all();

        // Agregar áreas/cargos que no existen
        $areasFaltantes = [];
        $cargosFaltantes = [];
        foreach ($pacientes as $p) {
            $an = $p['area_nombre'];
            $cn = $p['cargo_nombre'];
            if ($an && ! isset($areasMap[$an])) {
                $areasFaltantes[$an] = true;
            }
            if ($cn && ! isset($cargosMap[$cn])) {
                $cargosFaltantes[$cn] = true;
            }
        }

        if ($areasFaltantes) {
            $this->command->info('  Insertando ' . count($areasFaltantes) . ' áreas nuevas...');
            foreach (array_keys($areasFaltantes) as $nombre) {
                $area = Area::query()->create(['nombre' => $nombre, 'activo' => true]);
                $areasMap[$nombre] = $area->id;
            }
        }
        if ($cargosFaltantes) {
            $this->command->info('  Insertando ' . count($cargosFaltantes) . ' cargos nuevos...');
            foreach (array_keys($cargosFaltantes) as $nombre) {
                $cargo = Cargo::query()->create(['nombre' => $nombre, 'activo' => true]);
                $cargosMap[$nombre] = $cargo->id;
            }
        }

        // === FASE 4: Insertar pacientes ===
        $this->command->info('Insertando pacientes...');
        $insertados = 0;
        $examenesInsert = 0;
        $visitasInsert = 0;

        DB::transaction(function () use ($pacientes, $areasMap, $cargosMap, &$insertados, &$examenesInsert, &$visitasInsert) {
            foreach ($pacientes as $cedula => $data) {
                // Insertar paciente
                $paciente = MedicoPaciente::query()->create([
                    'cedula'            => $cedula,
                    'nombres'           => $data['nombres'],
                    'area_id'           => $data['area_nombre'] ? ($areasMap[$data['area_nombre']] ?? null) : null,
                    'cargo_id'          => $data['cargo_nombre'] ? ($cargosMap[$data['cargo_nombre']] ?? null) : null,
                    'fecha_ingreso'     => $data['fecha_ingreso'],
                    'patologias'        => $data['patologias'],
                    'vacunas'           => $data['vacunas'],
                    'fichas_anteriores' => $data['fichas_anteriores'],
                    'telefono'          => $data['telefono'],
                    'tipo'              => 'colaborador',
                    'activo'            => true,
                ]);
                $insertados++;

                // Insertar exámenes
                foreach ($data['examenes'] as $tipo => $fecha) {
                    if ($fecha) {
                        MedicoPacienteExamen::query()->create([
                            'paciente_id' => $paciente->id,
                            'tipo'        => $tipo,
                            'fecha'       => $fecha,
                        ]);
                        $examenesInsert++;
                    }
                }

                // Insertar visitas
                foreach ($data['visitas'] as $anio => $fecha) {
                    MedicoPacienteVisita::query()->create([
                        'paciente_id' => $paciente->id,
                        'anio'        => $anio,
                        'fecha'       => $fecha,
                    ]);
                    $visitasInsert++;
                }
            }
        });

        $this->command->info("✓ Pacientes: $insertados");
        $this->command->info("✓ Exámenes: $examenesInsert");
        $this->command->info("✓ Visitas: $visitasInsert");

        $this->command->info('=== Importación de nómina completada ===');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function normalizar(mixed $valor): string
    {
        $v = trim((string) $valor);
        $v = preg_replace('/\s+/', ' ', $v);
        return mb_strtoupper($v, 'UTF-8');
    }

    private function normalizarTexto(mixed $valor): ?string
    {
        $v = trim((string) $valor);
        if ($v === '' || $v === '0') {
            return null;
        }
        return $v;
    }

    private function normalizarCedula(mixed $valor): string
    {
        // Limpiar: solo dígitos, eliminar espacios, puntos, guiones
        $v = trim((string) $valor);
        $v = preg_replace('/[^0-9]/', '', $v);
        return $v;
    }

    private function normalizarTelefono(mixed $valor): ?string
    {
        $v = trim((string) $valor);
        if ($v === '' || $v === '0') {
            return null;
        }
        // Limpiar formato
        $v = preg_replace('/[^0-9,;\-\/\s]/', '', $v);
        return $v ?: null;
    }

    private function excelDate(mixed $valor): ?string
    {
        if ($valor === null || $valor === '' || $valor === 0) {
            return null;
        }

        try {
            if (is_numeric($valor)) {
                $dt = Date::excelToDateTimeObject((float) $valor);
                return $dt->format('Y-m-d');
            }

            // Intentar parsear string
            $ts = strtotime((string) $valor);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
