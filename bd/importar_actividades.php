<?php
/**
 * Script de importación UNSPSC
 * Carga el clasificador de bienes y servicios a la tabla actividades.
 *
 * Uso: php bd/importar_actividades.php
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../bootstrap/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$archivoExcel = __DIR__.'/unspsc.xlsx';

// ── 1. Descargar si no existe ────────────────────────────────
if (!file_exists($archivoExcel)) {
    echo "Descargando clasificador UNSPSC...\n";
    $url = 'https://a.storyblok.com/f/167454/x/8db69f44cd/unspcs-clasificador-de-bienes-y-servicios-de-naciones-unidas-en-espanol.xlsx';
    $contenido = file_get_contents($url);

    if (!$contenido) {
        exit("Error: No se pudo descargar el archivo.\n");
    }

    file_put_contents($archivoExcel, $contenido);
    echo "Archivo descargado correctamente.\n";
} else {
    echo "Archivo ya existe, usando caché local.\n";
}

// ── 2. Leer con iterador (evita el error con imágenes embebidas) ──
echo "Leyendo Excel...\n";

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);   // <-- ignora imágenes, estilos, etc.
$spreadsheet = $reader->load($archivoExcel);
$hoja = $spreadsheet->getActiveSheet();

// ── 3. Importar fila por fila en lotes ──────────────────────
$lote = [];
$tamanoLote = 500;
$total = 0;
$errores = 0;
$filaNum = 0;

foreach ($hoja->getRowIterator() as $fila) {
    ++$filaNum;

    // Saltar cabecera
    if ($filaNum === 1) {
        continue;
    }

    $celdas = [];
    foreach ($fila->getCellIterator('A', 'H') as $celda) {
        $celdas[] = $celda->getValue();
    }

    // Ignorar filas completamente vacías
    if (empty(array_filter($celdas, fn ($v) => $v !== null && $v !== ''))) {
        continue;
    }

    $lote[] = [
        'codigo_segmento' => (int) ($celdas[0] ?? 0),
        'segmento' => mb_substr(trim((string) ($celdas[1] ?? '')), 0, 200),
        'codigo_familia' => (int) ($celdas[2] ?? 0),
        'familia' => mb_substr(trim((string) ($celdas[3] ?? '')), 0, 200),
        'codigo_clase' => (int) ($celdas[4] ?? 0),
        'clase' => mb_substr(trim((string) ($celdas[5] ?? '')), 0, 200),
        'codigo_producto' => (int) ($celdas[6] ?? 0),
        'producto' => mb_substr(trim((string) ($celdas[7] ?? '')), 0, 200),
    ];

    if (count($lote) >= $tamanoLote) {
        try {
            Illuminate\Database\Capsule\Manager::table('actividades')->insert($lote);
            $total += count($lote);
            echo "  → {$total} registros insertados...\n";
        } catch (Exception $e) {
            ++$errores;
            echo '  ✗ Error: '.$e->getMessage()."\n";
        }
        $lote = [];
    }
}

// Insertar restantes
if (!empty($lote)) {
    try {
        Illuminate\Database\Capsule\Manager::table('actividades')->insert($lote);
        $total += count($lote);
    } catch (Exception $e) {
        ++$errores;
        echo '  ✗ Error en lote final: '.$e->getMessage()."\n";
    }
}

echo "\n✓ Importación completada: {$total} registros insertados, {$errores} errores.\n";
