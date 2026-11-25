<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../nucleo/comun.php';
requerir_rol('editor', 'coordinacion');
require_once __DIR__ . '/../includes/recorridos.php';

$recorrido = rec_recorrido_admin(rec_id($_GET['id'] ?? null));
rec_confirmar_baja($recorrido, 'recorrido', 'index.php', 'escenas, puntos y medios');
