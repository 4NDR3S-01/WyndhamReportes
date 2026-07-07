<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MedicoCatalogosSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Mapa de columna Excel → tabla destino.
     * Cada entrada: [columna, tabla]
     * La fila 3 contiene el nombre del catálogo, filas 4+ los valores.
     */
    private const CATALOG_MAP = [
        'A' => 'areas',
        'B' => 'cargos',
        'D' => 'tipos_certificado',
        'F' => 'causas',
        'H' => 'medicamentos',
        'J' => 'entidades_certificado',
        'L' => 'tipos_descanso',
        'N' => 'tipos_salida',
        'P' => 'diagnosticos',
        'X' => 'incidentes',
    ];

    public function run(): void
    {
        $ruta = base_path('assets/DEPARTAMENTO_MEDICO.xlsx');

        if (! file_exists($ruta)) {
            $this->command?->error("No se encontró el Excel: {$ruta}");
            return;
        }

        $spreadsheet = IOFactory::load($ruta);
        $sheet = $spreadsheet->getSheetByName('BASE DE DATOS');

        if (! $sheet) {
            $this->command?->error('No se encontró la hoja BASE DE DATOS');
            return;
        }

        $maxRow = $sheet->getHighestRow();
        $totalInsertados = 0;

        foreach (self::CATALOG_MAP as $columna => $tabla) {
            $valores = $this->extraerValores($sheet, $columna, $maxRow);
            // También recolectamos valores de otras hojas para áreas y cargos
            if ($tabla === 'areas') {
                $valores = array_merge($valores, $this->extraerAreasDeNomina($spreadsheet));
            }
            if ($tabla === 'cargos') {
                $valores = array_merge($valores, $this->extraerCargosDeNomina($spreadsheet));
            }

            $insertados = $this->insertarCatalogo($tabla, $valores);
            $totalInsertados += $insertados;

            if ($this->command) {
                $this->command->line("  <info>{$tabla}</info>: {$insertados} registros");
            }
        }

        if ($this->command) {
            $this->command->info("Total: {$totalInsertados} registros insertados en " . count(self::CATALOG_MAP) . " catálogos.");
        }

        // Reportar posibles duplicados fuzzy en catálogos clave
        $this->reportarSimilares('medicamentos');
        $this->reportarSimilares('diagnosticos');
    }

    private function extraerValores($sheet, string $columna, int $maxRow): array
    {
        $valores = [];
        for ($r = 4; $r <= $maxRow; $r++) {
            $val = $sheet->getCell($columna . $r)->getValue();
            if ($val === null || $val === '') continue;
            if (is_numeric($val)) continue; // saltar fechas Excel

            $nombre = $this->normalizar((string) $val);
            if ($nombre !== '') {
                $valores[] = $nombre;
            }
        }
        return array_values(array_unique($valores));
    }

    private function extraerAreasDeNomina($spreadsheet): array
    {
        $valores = [];
        foreach (['NOMINA', 'Copia de NOMINA', 'PARTES DIARIO 2025', 'PARTES DIARIO 2026'] as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (! $sheet) continue;

            $startRow = in_array($sheetName, ['NOMINA'], true) ? 3 : (
                in_array($sheetName, ['Copia de NOMINA'], true) ? 2 : 7
            );
            $colD = in_array($sheetName, ['PARTES DIARIO 2025', 'PARTES DIARIO 2026'], true) ? 'D' : 'D';

            for ($r = $startRow; $r <= $sheet->getHighestRow(); $r++) {
                $val = $sheet->getCell($colD . $r)->getValue();
                if ($val === null || $val === '') continue;
                if (is_numeric($val)) continue;

                $nombre = $this->normalizar((string) $val);
                if ($nombre !== '' && ! str_contains($nombre, 'AREA') && ! str_contains($nombre, 'PUESTO')) {
                    $valores[] = $nombre;
                }
            }
        }
        return array_values(array_unique($valores));
    }

    private function extraerCargosDeNomina($spreadsheet): array
    {
        $valores = [];
        foreach (['NOMINA', 'Copia de NOMINA', 'PARTES DIARIO 2025', 'PARTES DIARIO 2026'] as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (! $sheet) continue;

            $startRow = in_array($sheetName, ['NOMINA'], true) ? 3 : (
                in_array($sheetName, ['Copia de NOMINA'], true) ? 2 : 7
            );
            $colE = 'E';

            for ($r = $startRow; $r <= $sheet->getHighestRow(); $r++) {
                $val = $sheet->getCell($colE . $r)->getValue();
                if ($val === null || $val === '') continue;
                if (is_numeric($val)) continue;

                $nombre = $this->normalizar((string) $val);
                if ($nombre !== '' && ! str_contains($nombre, 'PUESTO') && ! str_contains($nombre, 'CARGO')) {
                    $valores[] = $nombre;
                }
            }
        }
        return array_values(array_unique($valores));
    }

    /**
     * Normaliza un valor: trim, uppercase, remover acentos, puntuación y caracteres especiales.
     * Maneja inconsistencias comunes en datos de Excel.
     */
    private function normalizar(string $valor): string
    {
        $valor = trim($valor);

        // Convertir a mayúsculas
        $valor = mb_strtoupper($valor, 'UTF-8');

        // Remover acentos y diacríticos (Á→A, É→E, etc.)
        $valor = $this->quitarAcentos($valor);

        // Reemplazar guiones y barras por espacios (PARACETAMOL-500MG → PARACETAMOL 500MG)
        $valor = preg_replace('/[\-\/\|\\\\]+/', ' ', $valor);

        // Remover puntuación excepto espacios y números
        $valor = preg_replace('/[,.!;:¿?¡"\'#@&*()\[\]{}<>]/u', '', $valor);

        // Normalizar paréntesis sobrantes como espacios
        $valor = str_replace(['(', ')', '[', ']'], ' ', $valor);

        // Colapsar múltiples espacios en uno solo
        $valor = preg_replace('/\s+/', ' ', $valor);

        // Trim final por si la puntuación dejó espacios en los bordes
        $valor = trim($valor);

        return $valor;
    }

    /**
     * Quita acentos y diacríticos de un string UTF-8.
     */
    private function quitarAcentos(string $texto): string
    {
        $mapa = [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ÿ' => 'Y',
            'Ç' => 'C', 'Ñ' => 'N',
        ];

        return strtr($texto, $mapa);
    }

    private function insertarCatalogo(string $tabla, array $valores): int
    {
        $insertados = 0;
        $ahora = now();

        foreach ($valores as $nombre) {
            if ($nombre === '') continue;
            // Usar insertOrIgnore para no duplicar
            DB::table($tabla)->insertOrIgnore([
                'nombre' => $nombre,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
            $insertados++;
        }

        return $insertados;
    }

    /**
     * Detecta y reporta nombres muy similares en un catálogo
     * que podrían ser duplicados por error de tipeo.
     */
    private function reportarSimilares(string $tabla): void
    {
        if (! $this->command) return;

        $nombres = DB::table($tabla)->pluck('nombre')->sort()->values()->all();
        $similares = [];

        for ($i = 0; $i < count($nombres); $i++) {
            for ($j = $i + 1; $j < count($nombres); $j++) {
                $dist = levenshtein($nombres[$i], $nombres[$j]);
                $maxLen = max(mb_strlen($nombres[$i]), mb_strlen($nombres[$j]));

                // Solo reportar si la distancia es ≤ 20% de la longitud máxima
                if ($maxLen > 3 && $dist > 0 && $dist <= ceil($maxLen * 0.2)) {
                    $similares[] = "    \"{$nombres[$i]}\" ↔ \"{$nombres[$j]}\" (distancia: {$dist})";
                }
            }
        }

        if ($similares) {
            $this->command->warn("  ⚠ Posibles duplicados en <comment>{$tabla}</comment>:");
            foreach ($similares as $s) {
                $this->command->line($s);
            }
        }
    }
}
