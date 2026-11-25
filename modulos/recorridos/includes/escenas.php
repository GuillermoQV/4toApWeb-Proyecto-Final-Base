<?php
declare(strict_types=1);

function rec_escenas(int $recorridoId): array
{
    return rec_consulta('SELECT * FROM rec_escenas WHERE recorrido_id = ? ORDER BY orden, id', [$recorridoId])->fetchAll();
}

function rec_escena_admin(?int $id): array
{
    $escena = rec_consulta('SELECT * FROM rec_escenas WHERE id = ?', [$id])->fetch();
    if (!$escena) {
        no_encontrado('Escena no encontrada.');
    }
    // Verificar el recorrido también verifica la carrera del editor.
    $recorrido = rec_recorrido_admin((int)$escena['recorrido_id']);
    $escena['estado'] = $recorrido['estado'];
    return $escena;
}

function rec_puntos(int $escenaId): array
{
    return rec_consulta('SELECT * FROM rec_puntos WHERE escena_id = ? ORDER BY id', [$escenaId])->fetchAll();
}

function rec_punto_admin(?int $id, int $escenaId): array
{
    $punto = rec_consulta('SELECT * FROM rec_puntos WHERE id = ? AND escena_id = ?', [$id, $escenaId])->fetch();
    if (!$punto) {
        no_encontrado('Punto no encontrado.');
    }
    return $punto;
}

// Dentro de la transacción, movemos un id y volvemos a numerar desde 1.
function rec_renumerar(int $recorridoId, ?int $mover = null, int $posicion = 1): void
{
    $ids = array_map('intval', array_column(rec_escenas($recorridoId), 'id'));
    if ($mover !== null) {
        $anterior = array_search($mover, $ids, true);
        if ($anterior === false || $posicion < 1 || $posicion > count($ids)) {
            throw new DomainException('Elige una escena y una posición de este recorrido.');
        }
        array_splice($ids, $anterior, 1);
        array_splice($ids, $posicion - 1, 0, [$mover]);
    }
    $consulta = bd()->prepare('UPDATE rec_escenas SET orden = ? WHERE id = ?');
    foreach ($ids as $indice => $id) {
        $consulta->execute([$indice + 1, $id]);
    }
}

function rec_guardar_escena(int $recorridoId, ?int $escenaId, string $titulo, string $descripcion, int $orden): void
{
    rec_transaccion($recorridoId, function () use ($recorridoId, $escenaId, $titulo, $descripcion, $orden): void {
        if ($escenaId === null) {
            rec_consulta('INSERT INTO rec_escenas (recorrido_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)',
                [$recorridoId, $titulo, $descripcion, $orden]);
            $escenaId = (int)bd()->lastInsertId();
        } else {
            rec_consulta('UPDATE rec_escenas SET titulo = ?, descripcion = ? WHERE id = ? AND recorrido_id = ?',
                [$titulo, $descripcion, $escenaId, $recorridoId]);
        }
        rec_renumerar($recorridoId, $escenaId, $orden);
    });
}
