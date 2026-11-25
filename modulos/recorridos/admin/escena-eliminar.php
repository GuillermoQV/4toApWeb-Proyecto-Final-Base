<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$escena = rec_escena_admin(rec_id($_GET['id'] ?? null));
$url = 'escenas.php?recorrido_id=' . (int)$escena['recorrido_id'];
rec_confirmar_baja($escena, 'escena', $url, 'puntos y medios');
