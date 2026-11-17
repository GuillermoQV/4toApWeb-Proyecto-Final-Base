<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

rec_token_post();
$recorrido = rec_recorrido_admin(rec_id($_POST['id'] ?? null));
$accion = $_POST['accion'] ?? null;

if ($accion === 'publicar') {
    $consulta = bd()->prepare(
        "UPDATE rec_recorridos SET estado = 'publicado'
         WHERE id = ? AND estado = 'borrador' AND EXISTS (
            SELECT 1 FROM rec_escenas e JOIN rec_medios m ON m.escena_id = e.id
             WHERE e.recorrido_id = rec_recorridos.id AND m.punto_id IS NULL
               AND m.tipo IN ('imagen', 'video')
         )"
    );
    $consulta->execute([$recorrido['id']]);
    $correcto = $consulta->rowCount() === 1;
    mensaje($correcto ? 'exito' : 'error', $correcto
        ? 'Recorrido publicado.'
        : 'Para publicar, el recorrido debe estar en borrador y tener una escena con imagen o video.');
} elseif ($accion === 'archivar') {
    $consulta = bd()->prepare("UPDATE rec_recorridos SET estado = 'archivado' WHERE id = ? AND estado = 'publicado'");
    $consulta->execute([$recorrido['id']]);
    $correcto = $consulta->rowCount() === 1;
    mensaje($correcto ? 'exito' : 'error', $correcto ? 'Recorrido archivado.' : 'El recorrido debe estar publicado para archivarlo.');
} elseif ($accion === 'desarchivar') {
    $consulta = bd()->prepare("UPDATE rec_recorridos SET estado = 'borrador' WHERE id = ? AND estado = 'archivado'");
    $consulta->execute([$recorrido['id']]);
    $correcto = $consulta->rowCount() === 1;
    mensaje($correcto ? 'exito' : 'error', $correcto
        ? 'Recorrido desarchivado. Volvió a borrador.'
        : 'El recorrido debe estar archivado para desarchivarlo.');
} else {
    mensaje('error', 'Selecciona una acción válida.');
}

redirigir('index.php');
