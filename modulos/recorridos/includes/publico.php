<?php

declare(strict_types=1);

function rec_listado_publico(?int $carreraId): array
{
    $sql = "SELECT r.id, r.titulo, r.descripcion, c.nombre AS carrera
            FROM rec_recorridos r JOIN carreras c ON c.id = r.carrera_id
            WHERE r.estado = 'publicado' AND c.activa = 1";
    $orden = ' ORDER BY c.nombre, r.titulo, r.id';
    if ($carreraId === null) {
        return bd()->query($sql . $orden)->fetchAll();
    }
    $consulta = bd()->prepare($sql . ' AND r.carrera_id = ?' . $orden);
    $consulta->execute([$carreraId]);
    return $consulta->fetchAll();
}

function rec_recorrido_publico(int $id): ?array
{
    $consulta = bd()->prepare(
        "SELECT r.id, r.titulo, r.descripcion, c.nombre AS carrera, c.area
         FROM rec_recorridos r JOIN carreras c ON c.id = r.carrera_id
         WHERE r.id = ? AND r.estado = 'publicado' AND c.activa = 1"
    );
    $consulta->execute([$id]);
    return $consulta->fetch() ?: null;
}

function rec_escenas_publicas(int $recorridoId): array
{
    $consulta = bd()->prepare('SELECT id, titulo, descripcion FROM rec_escenas WHERE recorrido_id = ? ORDER BY orden, id');
    $consulta->execute([$recorridoId]);
    return $consulta->fetchAll();
}

function rec_contenido_publico(int $escenaId): array
{
    $consulta = bd()->prepare('SELECT id, titulo, descripcion, x, y FROM rec_puntos WHERE escena_id = ? ORDER BY id');
    $consulta->execute([$escenaId]);
    $puntos = $consulta->fetchAll();
    $consulta = bd()->prepare('SELECT id, tipo, punto_id FROM rec_medios WHERE escena_id = ? ORDER BY id');
    $consulta->execute([$escenaId]);

    $medios = [];
    $imagenesDePunto = [];
    foreach ($consulta->fetchAll() as $medio) {
        if ($medio['punto_id'] === null) {
            $medios[] = $medio;
        } elseif ($medio['tipo'] === 'imagen') {
            $imagenesDePunto[(int)$medio['punto_id']][] = $medio;
        }
    }

    return [$puntos, $medios, $imagenesDePunto];
}
