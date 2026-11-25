<?php
declare(strict_types=1);

// Cada pantalla carga estas funciones después del núcleo del maestro.
require_once __DIR__ . '/plantilla.php';
require_once __DIR__ . '/paginacion.php';
require_once __DIR__ . '/escenas.php';
require_once __DIR__ . '/medios.php';
require_once __DIR__ . '/publico.php';

function rec_consulta(string $sql, array $valores = []): PDOStatement
{
    $consulta = bd()->prepare($sql);
    $consulta->execute($valores);
    return $consulta;
}

function rec_id(mixed $valor): ?int
{
    return is_string($valor) ? entero($valor) : null;
}

function rec_es_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Los formularios conservan el texto si hay un error de validación.
function rec_textos(?array $fila): array
{
    if (!rec_es_post()) {
        return [$fila['titulo'] ?? '', $fila['descripcion'] ?? ''];
    }
    $titulo = is_string($_POST['titulo'] ?? null) ? post('titulo') : '';
    $descripcion = is_string($_POST['descripcion'] ?? null) ? post('descripcion') : '';
    return [$titulo, $descripcion];
}

function rec_validar_textos(string $titulo, string $descripcion): void
{
    if ($titulo === '' || mb_strlen($titulo) > 160 || mb_strlen($descripcion) > 8000) {
        throw new DomainException('Escribe un título de 1 a 160 caracteres. La descripción admite hasta 8000.');
    }
}

// Un formulario POST comprueba el token, guarda y redirige. Los avisos usan el núcleo.
function rec_post(string $destino, callable $guardar, string $exito): void
{
    if (!rec_es_post()) {
        return;
    }
    if (!is_string($_POST['token'] ?? null)) {
        http_response_code(400);
        exit('Petición no válida.');
    }
    verificar_token();
    try {
        $guardar();
        mensaje('exito', $exito);
        redirigir($destino);
    } catch (DomainException $error) {
        mensaje('error', $error->getMessage());
    } catch (Throwable $error) {
        error_log('REC: ' . $error->getMessage());
        mensaje('error', 'No se pudo guardar el cambio.');
    }
}

function rec_recorrido_admin(?int $id, bool $bloquear = false): array
{
    $sql = 'SELECT * FROM rec_recorridos WHERE id = ?';
    $recorrido = rec_consulta($sql . ($bloquear ? ' FOR UPDATE' : ''), [$id])->fetch();
    if (!$recorrido) {
        if ($bloquear) {
            throw new DomainException('El recorrido ya no está disponible.');
        }
        no_encontrado('Recorrido no encontrado.');
    }
    requerir_carrera((int)$recorrido['carrera_id']);
    return $recorrido;
}

// El bloqueo evita que dos cambios de un mismo recorrido se pisen.
function rec_transaccion(int $id, callable $cambiar): mixed
{
    $conexion = bd();
    $conexion->beginTransaction();
    try {
        $resultado = $cambiar(rec_recorrido_admin($id, true));
        $conexion->commit();
        return $resultado;
    } catch (Throwable $error) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        throw $error;
    }
}

function rec_tiene_medio_principal(int $id): bool
{
    return (bool)rec_consulta(
        "SELECT 1 FROM rec_escenas e JOIN rec_medios m ON m.escena_id = e.id
         WHERE e.recorrido_id = ? AND m.punto_id IS NULL AND m.tipo IN ('imagen', 'video') LIMIT 1",
        [$id]
    )->fetchColumn();
}
