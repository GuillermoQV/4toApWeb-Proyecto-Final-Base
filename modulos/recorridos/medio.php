<?php

declare(strict_types=1);

require_once __DIR__ . '/../../nucleo/comun.php';
require_once __DIR__ . '/includes/medios.php';

header('Cache-Control: no-store');

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo !== 'GET' && $metodo !== 'HEAD') {
    header('Allow: GET, HEAD');
    http_response_code(405);
    exit;
}

$entradaId = $_GET['id'] ?? null;
$id = is_string($entradaId) ? entero($entradaId) : null;
$medio = $id === null ? null : rec_obtener_medio($id);
if ($medio === null) {
    no_encontrado('Medio no disponible.');
}

if ($medio['estado'] !== 'publicado' && rol() !== 'coordinacion'
    && !(rol() === 'editor' && carrera_del_usuario() === (int)$medio['carrera_id'])) {
    no_encontrado('Medio no disponible.');
}

try {
    $ruta = rec_ruta_medio($medio['nombre_archivo']);
} catch (RuntimeException $error) {
    error_log('REC: ' . $error->getMessage());
    no_encontrado('Medio no disponible.');
}

if (!is_file($ruta) || is_link($ruta) || !is_readable($ruta)) {
    no_encontrado('Medio no disponible.');
}
$tamano = filesize($ruta);
$tipo = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
if ($tamano === false || $tamano < 1
    || !in_array($tipo, REC_TIPOS_MEDIO[$medio['tipo']]['mime'], true)) {
    no_encontrado('Medio no disponible.');
}

session_write_close();

$descarga = ($_GET['descarga'] ?? null) === '1';
if ($descarga && $metodo === 'GET') {
    entregar_archivo($ruta, $medio['nombre_archivo']);
}

$inicio = 0;
$fin = $tamano - 1;
$rango = $_SERVER['HTTP_RANGE'] ?? '';
if (!$descarga && $metodo === 'GET' && preg_match('/\Abytes=(\d*)-(\d*)\z/', $rango, $partes)) {
    if ($partes[1] !== '') {
        $inicio = (int)$partes[1];
        if ($partes[2] !== '') {
            $fin = min((int)$partes[2], $fin);
        }
    } elseif ($partes[2] !== '' && (int)$partes[2] > 0) {
        $inicio = max(0, $tamano - (int)$partes[2]);
    } else {
        $inicio = $tamano;
    }

    if ($inicio > $fin) {
        header("Content-Range: bytes */{$tamano}");
        http_response_code(416);
        exit;
    }
    header("Content-Range: bytes {$inicio}-{$fin}/{$tamano}");
    http_response_code(206);
}

$restante = $fin - $inicio + 1;
$archivo = $metodo === 'GET' ? fopen($ruta, 'rb') : null;
if ($metodo === 'GET' && $archivo === false) {
    header_remove('Content-Range');
    no_encontrado('Medio no disponible.');
}

header('Content-Type: ' . $tipo);
header('Content-Length: ' . $restante);
header('Accept-Ranges: bytes');
header('Content-Disposition: ' . ($descarga ? 'attachment' : 'inline') . '; filename="' . $medio['nombre_archivo'] . '"');
if ($metodo === 'HEAD') {
    exit;
}

fseek($archivo, $inicio);
while ($restante > 0) {
    $bloque = fread($archivo, min(65536, $restante));
    if ($bloque === false || $bloque === '') {
        break;
    }
    echo $bloque;
    $restante -= strlen($bloque);
}
fclose($archivo);
