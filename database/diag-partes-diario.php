<?php
/**
 * Script de diagnóstico para analizar las hojas PARTES DIARIO del Excel.
 * Ejecutar: php database/diag-partes-diario.php
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$ruta = __DIR__ . '/../assets/DEPARTAMENTO_MEDICO.xlsx';

if (! file_exists($ruta)) {
    die("ERROR: No se encontró el Excel en: {$ruta}\n");
}

$spreadsheet = IOFactory::load($ruta);

foreach (['PARTES DIARIO 2025', 'PARTES DIARIO 2026'] as $sheetName) {
    $sheet = $spreadsheet->getSheetByName($sheetName);
    if (! $sheet) {
        echo "Hoja '{$sheetName}' NO encontrada.\n\n";
        continue;
    }

    $maxRow = $sheet->getHighestRow();
    $maxColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
        $sheet->getHighestColumn()
    );

    echo "=== {$sheetName} ===\n";
    echo "Filas: {$maxRow}, Columnas: {$maxColIdx}\n\n";

    // Mostrar filas 1-15
    echo "--- PRIMERAS 15 FILAS ---\n";
    for ($r = 1; $r <= min(15, $maxRow); $r++) {
        $vals = [];
        for ($ci = 0; $ci < $maxColIdx; $ci++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
            $v = $sheet->getCell($colLetter . $r)->getValue();
            $vals[] = $v !== null && $v !== '' ? mb_substr((string)$v, 0, 35) : '·';
        }
        echo sprintf("F%2d: ", $r) . implode(' | ', $vals) . "\n";
    }

    // Muestra 3 filas del medio
    $mid = intdiv($maxRow, 2);
    echo "\n--- MUESTRA FILAS {$mid}–" . ($mid+2) . " ---\n";
    for ($r = $mid; $r <= min($mid + 2, $maxRow); $r++) {
        $vals = [];
        for ($ci = 0; $ci < $maxColIdx; $ci++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
            $v = $sheet->getCell($colLetter . $r)->getValue();
            $vals[] = $v !== null && $v !== '' ? mb_substr((string)$v, 0, 30) : '·';
        }
        echo sprintf("F%2d: ", $r) . implode(' | ', $vals) . "\n";
    }

    echo "\n";
}
