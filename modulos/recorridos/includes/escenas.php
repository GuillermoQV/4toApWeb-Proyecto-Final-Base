<?php

declare(strict_types=1);

function rec_escenas_admin(int $recorridoId): array
{
    $consulta = bd()->prepare('SELECT id, titulo, descripcion, orden FROM rec_escenas WHERE recorrido_id = ? ORDER BY orden, id');
    $consulta->execute([$recorridoId]);
    return $consulta->fetchAll();
}

function rec_escena_admin(?int $id): array
{
    if ($id === null) {
        no_encontrado('Escena no encontrada.');
    }
    $consulta = bd()->prepare(
        'SELECT e.id, e.recorrido_id, e.titulo, e.descripcion, e.orden, r.carrera_id, r.estado
         FROM rec_escenas e JOIN rec_recorridos r ON r.id = e.recorrido_id WHERE e.id = ?'
    );
    $consulta->execute([$id]);
    $escena = $consulta->fetch();
    if (!$escena) {
        no_encontrado('Escena no encontrada.');
    }
    requerir_carrera((int)$escena['carrera_id']);
    return $escena;
}

function rec_puntos_admin(int $escenaId): array
{
    $consulta = bd()->prepare('SELECT id, titulo, descripcion, x, y FROM rec_puntos WHERE escena_id = ? ORDER BY id');
    $consulta->execute([$escenaId]);
    return $consulta->fetchAll();
}

function rec_punto_admin(?int $id, int $escenaId): array
{
    if ($id === null) {
        no_encontrado('Punto no encontrado.');
    }
    $consulta = bd()->prepare('SELECT id, titulo, descripcion, x, y FROM rec_puntos WHERE id = ? AND escena_id = ?');
    $consulta->execute([$id, $escenaId]);
    $punto = $consulta->fetch();
    if (!$punto) {
        no_encontrado('Punto no encontrado.');
    }
    return $punto;
}

function rec_baja_escena_o_punto(array $escena, ?int $puntoId): void
{
    $bd = bd();
    $bd->beginTransaction();
    try {
        $recorrido = rec_recorrido_admin((int)$escena['recorrido_id'], true);
        if ($puntoId === null && $recorrido['estado'] === 'publicado') {
            throw new DomainException('Archiva el recorrido antes de eliminar una escena.');
        }
        $consulta = $bd->prepare('SELECT id FROM rec_escenas WHERE id = ? AND recorrido_id = ? FOR UPDATE');
        $consulta->execute([$escena['id'], $recorrido['id']]);
        if (!$consulta->fetchColumn()) {
            throw new DomainException('La escena ya no está disponible.');
        }
        if ($puntoId === null) {
            $consulta = $bd->prepare('SELECT nombre_archivo FROM rec_medios WHERE escena_id = ? FOR UPDATE');
            $consulta->execute([$escena['id']]);
            $archivos = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta = $bd->prepare('DELETE FROM rec_escenas WHERE id = ?');
            $consulta->execute([$escena['id']]);
        } else {
            $consulta = $bd->prepare('SELECT id FROM rec_puntos WHERE id = ? AND escena_id = ? FOR UPDATE');
            $consulta->execute([$puntoId, $escena['id']]);
            if (!$consulta->fetchColumn()) {
                throw new DomainException('El punto ya no está disponible.');
            }
            $consulta = $bd->prepare('SELECT nombre_archivo FROM rec_medios WHERE escena_id = ? AND punto_id = ? FOR UPDATE');
            $consulta->execute([$escena['id'], $puntoId]);
            $archivos = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta = $bd->prepare('DELETE FROM rec_puntos WHERE id = ? AND escena_id = ?');
            $consulta->execute([$puntoId, $escena['id']]);
        }
        $bd->commit();
    } catch (Throwable $error) {
        if ($bd->inTransaction()) {
            $bd->rollBack();
        }
        throw $error;
    }

    foreach ($archivos as $nombre) {
        try {
            rec_limpiar_medio($nombre);
        } catch (RuntimeException $error) {
            error_log('REC: ' . $error->getMessage());
        }
    }
}
