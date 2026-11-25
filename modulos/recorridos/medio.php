<?php
declare(strict_types=1);
require_once __DIR__ . '/../../nucleo/comun.php';
require_once __DIR__ . '/includes/recorridos.php';

header('Cache-Control: no-store');
$metodo = $_SERVER['REQUEST_METHOD'];
if (!in_array($metodo, ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD');
    http_response_code(405);
    exit;
}

$medio = rec_consulta(
    'SELECT m.tipo, m.nombre_archivo, r.estado, r.carrera_id, c.activa
     FROM rec_medios m JOIN rec_escenas e ON e.id = m.escena_id
     JOIN rec_recorridos r ON r.id = e.recorrido_id JOIN carreras c ON c.id = r.carrera_id WHERE m.id = ?',
    [rec_id($_GET['id'] ?? null)]
)->fetch();
if (!$medio) {
    no_encontrado('Medio no disponible.');
}
$publico = $medio['estado'] === 'publicado' && (int)$medio['activa'] === 1;
$editor = rol() === 'editor' && carrera_del_usuario() === (int)$medio['carrera_id'];
if (!$publico && !$editor && rol() !== 'coordinacion') {
    no_encontrado('Medio no disponible.');
}
try {
    $ruta = rec_ruta_medio($medio['nombre_archivo']);
} catch (RuntimeException $error) {
    no_encontrado('Medio no disponible.');
}
$archivo = is_file($ruta) && !is_link($ruta) ? @fopen($ruta, 'rb') : false;
if (!$archivo) {
    no_encontrado('Medio no disponible.');
}
$tamano = fstat($archivo)['size'] ?? 0;
$tipo = @(new finfo(FILEINFO_MIME_TYPE))->file($ruta);
if (!$tamano || !in_array($tipo, REC_TIPOS_MEDIO[$medio['tipo']] ?? [], true)) {
    no_encontrado('Medio no disponible.');
}
session_write_close();

$descarga = ($_GET['descarga'] ?? '') === '1';
$inicio = 0;
$fin = $tamano - 1;
// Range permite adelantar un video pidiendo solo una parte del archivo.
if (!$descarga && $metodo === 'GET' && preg_match('/\Abytes=(\d*)-(\d*)\z/', $_SERVER['HTTP_RANGE'] ?? '', $rango)) {
    if ($rango[1] === '') {
        $inicio = max(0, $tamano - (int)$rango[2]);
    } else {
        $inicio = (int)$rango[1];
        $fin = $rango[2] === '' ? $fin : min($fin, (int)$rango[2]);
    }
    if ($inicio > $fin) {
        header("Content-Range: bytes */$tamano");
        http_response_code(416);
        exit;
    }
    header("Content-Range: bytes $inicio-$fin/$tamano");
    http_response_code(206);
}
$longitud = $fin - $inicio + 1;
header('Content-Type: ' . $tipo);
header('Content-Length: ' . $longitud);
header('Accept-Ranges: bytes');
$modo = $descarga ? 'attachment' : 'inline';
header("Content-Disposition: $modo; filename=\"" . $medio['nombre_archivo'] . '"');
if ($metodo === 'GET') {
    stream_copy_to_stream($archivo, fopen('php://output', 'wb'), $longitud, $inicio);
}
fclose($archivo);
