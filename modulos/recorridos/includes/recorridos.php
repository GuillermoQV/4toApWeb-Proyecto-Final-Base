<?php

declare(strict_types=1);

function rec_id(mixed $valor): ?int
{
    return is_string($valor) ? entero($valor) : null;
}

function rec_token_post(): void
{
    if (!is_string($_POST['token'] ?? null)) {
        http_response_code(400);
        exit('Petición no válida.');
    }
    verificar_token();
}

function rec_recorridos_admin(?int $carreraId): array
{
    $base = 'SELECT r.id, r.titulo, r.estado, c.nombre AS carrera
             FROM rec_recorridos r JOIN carreras c ON c.id = r.carrera_id';
    if ($carreraId === null) {
        return bd()->query($base . ' ORDER BY c.nombre, r.titulo, r.id')->fetchAll();
    }
    $consulta = bd()->prepare($base . ' WHERE r.carrera_id = ? ORDER BY r.titulo, r.id');
    $consulta->execute([$carreraId]);
    return $consulta->fetchAll();
}

function rec_recorrido_admin(?int $id, bool $bloquear = false): array
{
    if ($id === null) {
        no_encontrado('Recorrido no encontrado.');
    }
    $consulta = bd()->prepare(
        'SELECT id, carrera_id, titulo, descripcion, estado FROM rec_recorridos WHERE id = ?'
        . ($bloquear ? ' FOR UPDATE' : '')
    );
    $consulta->execute([$id]);
    $fila = $consulta->fetch();
    if (!$fila) {
        no_encontrado('Recorrido no encontrado.');
    }
    requerir_carrera((int)$fila['carrera_id']);
    return $fila;
}
