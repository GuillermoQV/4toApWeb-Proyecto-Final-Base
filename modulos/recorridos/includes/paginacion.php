<?php
declare(strict_types=1);

function rec_paginar(array $filas): array
{
    $paginas = max(1, (int)ceil(count($filas) / 9));
    $entrada = $_GET['pagina'] ?? null;
    $pagina = min($paginas, (is_string($entrada) ? entero($entrada) : null) ?? 1);
    return [array_slice($filas, ($pagina - 1) * 9, 9), $pagina, $paginas];
}

function rec_paginador(int $pagina, int $paginas, ?int $carreraId = null): void
{
    if ($paginas < 2) return;
    ?>
    <nav class="rec-navegacion" aria-label="Páginas de recorridos">
      <?php for ($i = 1; $i <= $paginas; $i++): ?>
        <a href="<?= e('?' . http_build_query(['pagina' => $i, 'carrera' => $carreraId])) ?>"
           class="boton <?= $pagina === $i ? 'boton--primario' : 'boton--secundario' ?>"
           <?= $pagina === $i ? 'aria-current="page"' : '' ?> aria-label="Página <?= e((string)$i) ?>"><?= e((string)$i) ?></a>
      <?php endfor; ?>
    </nav>
    <?php
}
