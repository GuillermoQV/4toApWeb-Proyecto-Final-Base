<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

if (!rec_es_post()) {
    header('Allow: POST');
    http_response_code(405);
    exit;
}
rec_post('index.php', function (): void {
    $id = rec_id($_POST['id'] ?? null);
    if ($id === null) {
        no_encontrado('Recorrido no encontrado.');
    }
    // Estado necesario y nuevo estado para cada botón.
    [$desde, $hasta] = match ($_POST['accion'] ?? '') {
        'publicar' => ['borrador', 'publicado'],
        'archivar' => ['publicado', 'archivado'],
        'desarchivar' => ['archivado', 'borrador'],
        default => throw new DomainException('Elige una acción válida.'),
    };
    rec_transaccion($id, function (array $recorrido) use ($id, $desde, $hasta): void {
        if ($recorrido['estado'] !== $desde) {
            throw new DomainException('La acción no está disponible para el estado actual.');
        }
        if ($hasta === 'publicado' && !rec_tiene_medio_principal($id)) {
            throw new DomainException('Agrega una imagen o un video principal a una escena antes de publicar.');
        }
        rec_consulta('UPDATE rec_recorridos SET estado = ? WHERE id = ?', [$hasta, $id]);
    });
}, 'Estado actualizado.');
redirigir('index.php');
