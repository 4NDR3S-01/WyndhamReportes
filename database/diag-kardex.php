<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

$ruta = __DIR__ . '/../assets/DEPARTAMENTO_MEDICO.xlsx';
$spreadsheet = IOFactory::load($ruta);
$sheet = $spreadsheet->getSheetByName('KARDEX 2026');
$maxRow = $sheet->getHighestRow();

echo "=== KARDEX 2026 — Todas las filas con datos ===\n\n";
for ($r = 1; $r <= $maxRow; $r++) {
    $vals = [];
    for ($ci = 0; $ci < 9; $ci++) {
        $colLetter = Coordinate::stringFromColumnIndex($ci + 1);
        $v = $sheet->getCell($colLetter . $r)->getValue();
        // Mostrar valor calculado para columna E (fórmulas)
        if ($colLetter === 'E' && $v === null) {
            $v = $sheet->getCell($colLetter . $r)->getCalculatedValue();
        }
        $vals[] = $v !== null && $v !== '' ? mb_substr((string)$v, 0, 28) : '·';
    }
    echo sprintf("F%2d: ", $r) . implode(' | ', $vals) . "\n";
}
