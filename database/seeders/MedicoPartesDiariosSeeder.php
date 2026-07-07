<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\EntidadCertificado;
use App\Models\Medicamento;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoParteMedicamento;
use App\Models\MedicoProducto;
use App\Models\MedicoProductoAlias;
use App\Models\TipoCertificado;
use App\Services\Medico\InventarioMedicoService;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MedicoPartesDiariosSeeder extends Seeder
{
    private InventarioMedicoService $inventarioService;

    // Catálogos cacheados (nombre_normalizado => id)
    private array $areasMap = [];
    private array $cargosMap = [];
    private array $causasMap = [];
    private array $diagnosticosMap = [];
    private array $medicamentosMap = [];
    private array $entidadesCertMap = [];
    private array $tiposCertMap = [];

    // Pacientes cacheados (nombre_normalizado => id)
    private array $pacientesPorNombre = [];

    // Para reporte final
    private array $medsNoEncontrados = [];
    private array $causasNoEncontradas = [];
    private array $diagnosticosNoEncontrados = [];

    public function __construct()
    {
        $this->inventarioService = app(InventarioMedicoService::class);
    }

    public function run(): void
    {
        $ruta = base_path('assets/DEPARTAMENTO_MEDICO.xlsx');

        if (! file_exists($ruta)) {
            $this->command?->error("No se encontró el Excel: {$ruta}");
            return;
        }

        $this->cargarCatalogos();
        $this->cargarPacientes();

        $spreadsheet = IOFactory::load($ruta);

        foreach (['PARTES DIARIO 2025', 'PARTES DIARIO 2026'] as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (! $sheet) {
                $this->command?->warn("Hoja '{$sheetName}' no encontrada, saltando...");
                continue;
            }

            $this->command?->info("=== Importando {$sheetName} ===");
            $this->procesarHoja($sheet, $sheetName);
        }

        $total = MedicoParteDiario::query()->count();
        $this->command?->info("Total de partes diarios en BD: {$total}");

        // Reporte de elementos no encontrados
        if ($this->medsNoEncontrados) {
            $this->command?->warn("\n Medicamentos no encontrados en catálogo (" . count($this->medsNoEncontrados) . "):");
            foreach (array_slice($this->medsNoEncontrados, 0, 30) as $m) {
                $this->command?->line("    - {$m}");
            }
        }
        if ($this->causasNoEncontradas) {
            $this->command?->warn("\n Causas no encontradas:");
            foreach ($this->causasNoEncontradas as $c) {
                $this->command?->line("    - {$c}");
            }
        }
        if ($this->diagnosticosNoEncontrados) {
            $this->command?->warn("\n Diagnósticos no encontrados:");
            foreach ($this->diagnosticosNoEncontrados as $d) {
                $this->command?->line("    - {$d}");
            }
        }
    }

    private function cargarCatalogos(): void
    {
        $this->areasMap = Area::query()->pluck('id', 'nombre')->all();
        $this->cargosMap = Cargo::query()->pluck('id', 'nombre')->all();
        $this->causasMap = Causa::query()->pluck('id', 'nombre')->all();
        $this->diagnosticosMap = Diagnostico::query()->pluck('id', 'nombre')->all();
        $this->medicamentosMap = Medicamento::query()->pluck('id', 'nombre')->all();
        $this->entidadesCertMap = EntidadCertificado::query()->pluck('id', 'nombre')->all();
        $this->tiposCertMap = TipoCertificado::query()->pluck('id', 'nombre')->all();
    }

    private function cargarPacientes(): void
    {
        $this->pacientesPorNombre = MedicoPaciente::query()
            ->pluck('id', 'nombres')
            ->all();
    }

    private function procesarHoja($sheet, string $sheetName): void
    {
        $maxRow = $sheet->getHighestDataRow();
        $maxColIdx = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        // Leer fila 6 (encabezados fijos)
        $headers = [];
        for ($ci = 0; $ci < $maxColIdx; $ci++) {
            $colLetter = Coordinate::stringFromColumnIndex($ci + 1);
            $headers[$colLetter] = $this->normalizar(
                (string) ($sheet->getCell($colLetter . '6')->getValue() ?? '')
            );
        }

        // Detectar si es 2025 (tiene CUADRO CLINICO) o 2026 (sin esas columnas)
        $es2025 = str_contains($sheetName, '2025');

        $this->command?->line("  Columnas detectadas: A=" . ($headers['A'] ?? '?') . " N=" . ($headers['N'] ?? '?') . " Q=" . ($headers['Q'] ?? '?') . " T=" . ($headers['T'] ?? '?'));

        $insertados = 0;
        $errores = 0;
        $duplicados = 0;
        $sinMedicamento = 0;
        $sinCausa = 0;

        // Datos empiezan en fila 7
        for ($r = 7; $r <= $maxRow; $r++) {
            // Leer fecha (col A) — si está vacía, saltar fila
            $fechaRaw = $sheet->getCell('A' . $r)->getValue();
            if ($fechaRaw === null || $fechaRaw === '') {
                continue;
            }

            $fecha = $this->parsearFecha($fechaRaw);
            if (! $fecha) {
                continue;
            }

            // Leer nombres (col B)
            $nombres = $this->normalizarNombrePersona(
                (string) ($sheet->getCell('B' . $r)->getValue() ?? '')
            );
            if ($nombres === '') {
                continue;
            }

            // --- Columnas fijas ---
            $edad = $this->parsearEntero($sheet->getCell('C' . $r)->getValue());
            $areaNombre = $this->normalizar((string) ($sheet->getCell('D' . $r)->getValue() ?? ''));
            $cargoNombre = $this->normalizar((string) ($sheet->getCell('E' . $r)->getValue() ?? ''));

            // Certificado
            $certEntidadRaw = $this->normalizar((string) ($sheet->getCell('F' . $r)->getValue() ?? ''));
            $certSubsidioRaw = $this->normalizar((string) ($sheet->getCell('G' . $r)->getValue() ?? ''));
            $certHoras = $this->parsearFloat($sheet->getCell('H' . $r)->getValue());
            $certDias = $this->parsearEntero($sheet->getCell('I' . $r)->getValue());
            $certFechaInicio = $this->parsearFecha($sheet->getCell('J' . $r)->getValue());
            $certFechaFin = $this->parsearFecha($sheet->getCell('K' . $r)->getValue());
            $medicoCertifica = trim((string) ($sheet->getCell('L' . $r)->getValue() ?? '')) ?: null;

            // Causa y diagnóstico
            $causaNombre = $this->normalizar((string) ($sheet->getCell('M' . $r)->getValue() ?? ''));
            $diagnosticoNombre = $this->normalizar((string) ($sheet->getCell('N' . $r)->getValue() ?? ''));

            // Columnas cambian según año
            if ($es2025) {
                // O=CUADRO CLINICO, P=EXAMEN FISICO, Q=MED1, R=MED2, S=MED3, T=OBSERVACION
                $cuadroClinico = trim((string) ($sheet->getCell('O' . $r)->getValue() ?? ''));
                $examenFisico = trim((string) ($sheet->getCell('P' . $r)->getValue() ?? ''));
                $med1 = trim((string) ($sheet->getCell('Q' . $r)->getValue() ?? ''));
                $med2 = trim((string) ($sheet->getCell('R' . $r)->getValue() ?? ''));
                $med3 = trim((string) ($sheet->getCell('S' . $r)->getValue() ?? ''));
                $observacion = trim((string) ($sheet->getCell('T' . $r)->getValue() ?? ''));
                // Construir observación completa
                $obsCompleta = [];
                if ($cuadroClinico) $obsCompleta[] = "CUADRO CLÍNICO: {$cuadroClinico}";
                if ($examenFisico) $obsCompleta[] = "EXAMEN FÍSICO: {$examenFisico}";
                if ($observacion) $obsCompleta[] = $observacion;
            } else {
                // 2026: O=MED1, P=MED2, Q=MED3, R=OBSERVACION
                $med1 = trim((string) ($sheet->getCell('O' . $r)->getValue() ?? ''));
                $med2 = trim((string) ($sheet->getCell('P' . $r)->getValue() ?? ''));
                $med3 = trim((string) ($sheet->getCell('Q' . $r)->getValue() ?? ''));
                $observacion = trim((string) ($sheet->getCell('R' . $r)->getValue() ?? ''));
                $obsCompleta = $observacion ? [$observacion] : [];
            }

            // --- Buscar IDs de catálogos ---
            $areaId = $areaNombre !== '' ? ($this->areasMap[$areaNombre] ?? null) : null;
            $cargoId = $cargoNombre !== '' ? ($this->cargosMap[$cargoNombre] ?? null) : null;

            // Causa
            $causaId = $this->buscarCausa($causaNombre);
            if (! $causaId && $causaNombre !== '') {
                $this->causasNoEncontradas[$causaNombre] = ($this->causasNoEncontradas[$causaNombre] ?? 0) + 1;
                $sinCausa++;
            }

            // Diagnóstico
            $diagnosticoId = $this->buscarDiagnostico($diagnosticoNombre);
            if (! $diagnosticoId && $diagnosticoNombre !== '') {
                $this->diagnosticosNoEncontrados[$diagnosticoNombre] = ($this->diagnosticosNoEncontrados[$diagnosticoNombre] ?? 0) + 1;
            }

            // Certificado
            $entidadCertId = $this->mapearEntidadCert($certEntidadRaw, $certSubsidioRaw);
            $tipoCertId = $certSubsidioRaw === 'SUBSIDIO' ? ($this->tiposCertMap['SUBSIDIO'] ?? null) : null;

            // Medicamentos
            $medsNombres = array_filter([$med1, $med2, $med3], fn ($m) => $m !== '');
            $medsProcesados = [];
            foreach ($medsNombres as $mNombre) {
                $res = $this->buscarMedicamento($mNombre);
                $medsProcesados[] = $res;
                if (! $res['medicamento_id']) {
                    $this->medsNoEncontrados[$mNombre] = ($this->medsNoEncontrados[$mNombre] ?? 0) + 1;
                }
            }

            if (empty($medsProcesados)) {
                $sinMedicamento++;
            }

            // --- Crear parte diario ---
            try {
                $hash = hash('sha256', implode('|', [
                    'excel_v2',
                    $sheetName,
                    (string) $r,
                    $fecha,
                    $nombres,
                ]));

                if (MedicoParteDiario::query()->where('hash_unico', $hash)->exists()) {
                    $duplicados++;
                    continue;
                }

                $parte = MedicoParteDiario::query()->create([
                    'fecha'                    => $fecha,
                    'nombres'                  => $nombres,
                    'edad'                     => $edad,
                    'area_id'                  => $areaId,
                    'cargo_id'                 => $cargoId,
                    'tipo_paciente'            => 'colaborador',
                    'tipo_certificado_id'      => $tipoCertId,
                    'entidad_certificado_id'   => $entidadCertId,
                    'horas_certificado'        => $certHoras,
                    'dias_certificado'         => $certDias,
                    'fecha_inicio_certificado' => $certFechaInicio,
                    'fecha_fin_certificado'    => $certFechaFin,
                    'medico_certifica'         => $medicoCertifica,
                    'causa_id'                 => $causaId,
                    'diagnostico_id'           => $diagnosticoId,
                    'observacion'              => implode(' | ', $obsCompleta) ?: null,
                    'hash_unico'               => $hash,
                ]);

                // Guardar medicamentos vinculados
                foreach ($medsProcesados as $med) {
                    MedicoParteMedicamento::query()->create([
                        'parte_diario_id' => $parte->id,
                        'medicamento_id'  => $med['medicamento_id'],
                        'nombre_original' => $med['nombre_original'],
                        'cantidad'        => $med['cantidad'],
                    ]);

                    // Registrar salida de inventario si hay producto vinculado
                    if ($med['medicamento_id']) {
                        $productos = MedicoProducto::query()
                            ->where('medicamento_id', $med['medicamento_id'])
                            ->where('activo', true)
                            ->get();

                        foreach ($productos as $producto) {
                            try {
                                $this->inventarioService->registrarMovimientoProducto(
                                    producto: $producto,
                                    tipo: 'salida',
                                    cantidad: $med['cantidad'],
                                    fecha: $fecha,
                                    origen: 'parte_diario',
                                    parte: $parte,
                                    observacion: "Importación Excel — {$sheetName} fila {$r}",
                                );
                            } catch (\Throwable $e) {
                                // Silencioso — el kardex puede fallar sin detener la importación
                            }
                        }
                    }

                    // Crear alias para búsqueda futura
                    $this->crearAliasPendiente($med['nombre_original']);
                }

                $insertados++;

            } catch (\Throwable $e) {
                $errores++;
                if ($errores <= 5) {
                    $this->command?->warn("    Error fila {$r}: {$e->getMessage()}");
                }
            }
        }

        $this->command?->info("  {$sheetName}: {$insertados} OK, {$duplicados} dups, {$sinMedicamento} sin medic, {$sinCausa} sin causa, {$errores} errores");
    }

    // ============================================================
    // BÚSQUEDA DE CATÁLOGOS
    // ============================================================

    private function buscarMedicamento(string $nombreOriginal): array
    {
        $normalizado = $this->normalizar($nombreOriginal);

        // 1. Coincidencia exacta
        if (isset($this->medicamentosMap[$normalizado])) {
            return [
                'medicamento_id'  => $this->medicamentosMap[$normalizado],
                'nombre_original' => $nombreOriginal,
                'cantidad'        => 1.0,
            ];
        }

        // 2. Coincidencia parcial (el nombre del catálogo contiene el texto)
        foreach ($this->medicamentosMap as $catNombre => $id) {
            if (str_contains($catNombre, $normalizado) || str_contains($normalizado, $catNombre)) {
                return [
                    'medicamento_id'  => $id,
                    'nombre_original' => $nombreOriginal,
                    'cantidad'        => 1.0,
                ];
            }
        }

        // 3. Levenshtein fuzzy (≤25% diferencia)
        $mejorDist = PHP_INT_MAX;
        $mejorId = null;
        foreach ($this->medicamentosMap as $catNombre => $id) {
            $dist = levenshtein($normalizado, $catNombre);
            $maxLen = max(mb_strlen($normalizado), mb_strlen($catNombre));
            if ($maxLen > 3 && $dist <= ceil($maxLen * 0.25) && $dist < $mejorDist) {
                $mejorDist = $dist;
                $mejorId = $id;
            }
        }

        return [
            'medicamento_id'  => $mejorId,
            'nombre_original' => $nombreOriginal,
            'cantidad'        => 1.0,
        ];
    }

    private function buscarCausa(string $nombre): ?int
    {
        if ($nombre === '') return null;

        // Mapa de equivalencias comunes del Excel
        $equivalencias = [
            'OTORRINOLARINGOLOGICA'   => 'OTORRINOLARINGOLOGICA',
            'TRAUMATOLOGICA'          => 'TRAUMATOLOGICA',
            'GASTROENTEROLOGICAS'     => 'GASTROENTEROLOGICA',
            'GASTROENTEROLOGICA'      => 'GASTROENTEROLOGICA',
            'ATENCION_MEDICA_GENERAL' => 'ATENCION MEDICA GENERAL',
            'FICHA_MEDICA'            => 'FICHA MEDICA',
        ];

        $nombreCanonico = $equivalencias[$nombre] ?? $nombre;

        if (isset($this->causasMap[$nombreCanonico])) {
            return $this->causasMap[$nombreCanonico];
        }

        // Fuzzy fallback
        return $this->fuzzyBuscar($nombreCanonico, $this->causasMap);
    }

    private function buscarDiagnostico(string $nombre): ?int
    {
        if ($nombre === '') return null;

        // El Excel trunca diagnósticos largos. Buscar por prefijo.
        foreach ($this->diagnosticosMap as $catNombre => $id) {
            if (str_starts_with($catNombre, $nombre) || str_starts_with($nombre, $catNombre)) {
                return $id;
            }
        }

        return $this->fuzzyBuscar($nombre, $this->diagnosticosMap);
    }

    private function mapearEntidadCert(string $certRaw, string $subsidioRaw): ?int
    {
        // Los valores en el Excel: "SIN CERTIFICADO", "IESS", "MSP", "DPTO MEDICO"
        $mapa = [
            'IESS'        => 'IESS',
            'MSP'         => 'MSP',
            'DPTO MEDICO' => 'DEPARTAMENTO MEDICO',
        ];

        if ($certRaw === 'SIN CERTIFICADO' || $certRaw === '') {
            return null;
        }

        $canonico = $mapa[$certRaw] ?? $certRaw;
        return $this->entidadesCertMap[$canonico] ?? null;
    }

    private function fuzzyBuscar(string $nombre, array $mapa): ?int
    {
        $mejorDist = PHP_INT_MAX;
        $mejorId = null;

        foreach ($mapa as $catNombre => $id) {
            $dist = levenshtein($nombre, $catNombre);
            $maxLen = max(mb_strlen($nombre), mb_strlen($catNombre));
            if ($maxLen > 4 && $dist <= ceil($maxLen * 0.3)) {
                if ($dist < $mejorDist) {
                    $mejorDist = $dist;
                    $mejorId = $id;
                }
            }
        }

        return $mejorId;
    }

    private function crearAliasPendiente(string $nombreOriginal): void
    {
        $normalizado = $this->normalizar($nombreOriginal);
        $producto = MedicoProducto::resolverPorNombre($normalizado);

        if ($producto) {
            MedicoProductoAlias::query()->firstOrCreate(
                ['alias_normalizado' => MedicoProducto::normalizarNombre($nombreOriginal)],
                [
                    'producto_id' => $producto->id,
                    'alias'       => $nombreOriginal,
                ]
            );
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function normalizar(string $valor): string
    {
        $v = trim($valor);
        $v = mb_strtoupper($v, 'UTF-8');
        $mapa = ['Á'=>'A','À'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A','Å'=>'A','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I','Ó'=>'O','Ò'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O','Ø'=>'O','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','Ÿ'=>'Y','Ç'=>'C','Ñ'=>'N'];
        $v = strtr($v, $mapa);
        $v = preg_replace('/[\-\/\|\\\\]+/', ' ', $v);
        $v = preg_replace('/[,.!;:¿?¡"\'#@&*()\[\]{}<>]/u', '', $v);
        $v = str_replace(['(', ')', '[', ']'], ' ', $v);
        $v = preg_replace('/\s+/', ' ', $v);
        return trim($v);
    }

    private function normalizarNombrePersona(string $valor): string
    {
        $v = trim($valor);
        $v = preg_replace('/\s+/', ' ', $v);
        return mb_strtoupper($v, 'UTF-8');
    }

    private function parsearFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') return null;
        try {
            if (is_numeric($valor) && $valor > 30000 && $valor < 80000) {
                $dt = Date::excelToDateTimeObject((float) $valor);
                return $dt->format('Y-m-d');
            }
            $ts = strtotime((string) $valor);
            if ($ts !== false && $ts > 0) return date('Y-m-d', $ts);
        } catch (\Throwable) {}
        return null;
    }

    private function parsearEntero(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') return null;
        return is_numeric($valor) ? (int) $valor : null;
    }

    private function parsearFloat(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') return null;
        if (is_numeric($valor)) return (float) $valor;
        $v = str_replace(',', '.', (string) $valor);
        return is_numeric($v) ? (float) $v : null;
    }
}
