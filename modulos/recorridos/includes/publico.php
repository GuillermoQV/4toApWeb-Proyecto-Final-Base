<?php
declare(strict_types=1);

function rec_listado(?int $carreraId, bool $soloPublicados = true): array
{
    $sql = 'SELECT r.*, c.nombre AS carrera FROM rec_recorridos r JOIN carreras c ON c.id = r.carrera_id WHERE 1 = 1';
    if ($soloPublicados) {
        $sql .= " AND r.estado = 'publicado' AND c.activa = 1";
    }
    if ($carreraId !== null) {
        $sql .= ' AND r.carrera_id = ?';
    }
    return rec_consulta($sql . ' ORDER BY c.nombre, r.titulo, r.id', $carreraId === null ? [] : [$carreraId])->fetchAll();
}

function rec_recorrido_publico(?int $id): array
{
    $fila = rec_consulta(
        "SELECT r.*, c.nombre AS carrera, c.area FROM rec_recorridos r JOIN carreras c ON c.id = r.carrera_id
         WHERE r.id = ? AND r.estado = 'publicado' AND c.activa = 1", [$id]
    )->fetch();
    if (!$fila) {
        no_encontrado('Recorrido no disponible.');
    }
    return $fila;
}

// El grupo 0 corresponde a los archivos principales de la escena.
function rec_archivos_por_punto(int $escenaId): array
{
    $grupos = [];
    $portada = false;
    foreach (rec_medios_de_escena($escenaId) as $medio) {
        $punto = $medio['punto_id'] ?? 0;
        if ($punto === 0 && $medio['tipo'] === 'imagen') {
            if ($portada) {
                continue;
            }
            $portada = true;
        }
        $grupos[$punto][] = $medio;
    }
    return $grupos;
}

function rec_mostrar_medio(array $medio, string $titulo): void
{
    $url = 'medio.php?id=' . (int)$medio['id'];
    if ($medio['tipo'] === 'imagen'): ?>
      <a class="rec-imagen" href="<?= e($url) ?>" data-visor="imagen" aria-haspopup="dialog" aria-label="<?= e('Ampliar imagen de ' . $titulo) ?>">
        <img src="<?= e($url) ?>" alt="<?= e('Imagen de ' . $titulo) ?>">
      </a>
    <?php elseif ($medio['tipo'] === 'video'): ?>
      <video controls playsinline preload="metadata" src="<?= e($url) ?>" aria-label="<?= e('Video de ' . $titulo) ?>"></video>
    <?php else: ?>
      <audio controls preload="none" src="<?= e($url) ?>" aria-label="<?= e('Narración de ' . $titulo) ?>"></audio>
    <?php endif;
}

function rec_enlace_escena(?array $escena, int $id, string $texto): void
{
    if ($escena): ?>
      <a class="boton boton--secundario" href="<?= e('ver.php?id=' . $id . '&escena=' . (int)$escena['id']) ?>#escenas"><?= e($texto) ?></a>
    <?php else: ?>
      <button type="button" class="boton boton--secundario" disabled><?= e($texto) ?></button>
    <?php endif;
}
