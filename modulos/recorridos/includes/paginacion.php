<?php
declare(strict_types=1);

// El catálogo y el panel muestran hasta nueve tarjetas por página.
function rec_paginar(array $filas): array
{
    $paginas = max(1, (int)ceil(count($filas) / 9));
    $pagina = min($paginas, rec_id($_GET['pagina'] ?? null) ?? 1);
    return [array_slice($filas, ($pagina - 1) * 9, 9), $pagina, $paginas];
}

function rec_paginador(int $pagina, int $paginas, ?int $carreraId = null): void
{
    if ($paginas < 2) {
        return;
    }
    ?>
    <nav class="fila rec-paginas" aria-label="Páginas de recorridos">
      <?php for ($i = 1; $i <= $paginas; $i++): ?>
        <a class="boton boton--secundario" href="?<?= e(http_build_query(['pagina' => $i, 'carrera' => $carreraId])) ?>"
           aria-label="Página <?= $i ?>" <?= $pagina === $i ? 'aria-current="page"' : '' ?>><?= $i ?></a>
      <?php endfor; ?>
    </nav>
    <?php
}
