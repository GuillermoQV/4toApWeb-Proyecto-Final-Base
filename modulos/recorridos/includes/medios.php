<?php
declare(strict_types=1);

const REC_TIPOS_MEDIO = [
    'imagen' => SOLO_IMAGEN,
    'video' => SOLO_VIDEO,
    'audio' => ['audio/mpeg', 'audio/mp4'],
];

// Apache solo permite llegar al archivo después de pasar por medio.php.
function rec_carpeta_medios(): string
{
    $raiz = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if (!$raiz || dirname($raiz) === $raiz) {
        throw new RuntimeException('No se pudo determinar la carpeta de medios.');
    }
    $carpeta = dirname($raiz) . DIRECTORY_SEPARATOR . 'ev-rec-medios';
    if (is_link($carpeta) || (file_exists($carpeta) && (!is_dir($carpeta) || strcasecmp(realpath($carpeta) ?: '', $carpeta) !== 0))) {
        throw new RuntimeException('ev-rec-medios debe ser una carpeta fuera de la raíz web.');
    }
    return $carpeta;
}

function rec_ruta_medio(string $nombre): string
{
    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp|mp4|mp3|m4a)\z/', $nombre)) {
        throw new RuntimeException('Nombre interno de archivo inválido.');
    }
    return rec_carpeta_medios() . DIRECTORY_SEPARATOR . $nombre;
}

function rec_medios_de_escena(int $escenaId): array
{
    return rec_consulta(
        'SELECT m.id, m.tipo, m.punto_id, p.titulo AS punto FROM rec_medios m
         LEFT JOIN rec_puntos p ON p.id = m.punto_id WHERE m.escena_id = ? ORDER BY m.id', [$escenaId]
    )->fetchAll();
}

function rec_subir_medio(array $escena, mixed $tipo, mixed $punto): void
{
    $escenaId = (int)$escena['id'];
    $puntoId = rec_id($punto);
    if (!in_array($tipo, ['imagen', 'video'], true) || ($punto !== '' && $puntoId === null)) {
        throw new DomainException('Selecciona un tipo y una ubicación válidos.');
    }
    try {
        $limite = $tipo === 'imagen' ? 5 : 30;
        $nombre = guardar_archivo($_FILES['archivo'] ?? [], rec_carpeta_medios(), REC_TIPOS_MEDIO[$tipo], $limite);
    } catch (RuntimeException $error) {
        throw new DomainException($error->getMessage());
    }
    try {
        rec_transaccion((int)$escena['recorrido_id'], function () use ($escenaId, $puntoId, $tipo, $nombre): void {
            if ($puntoId === null) {
                $portada = rec_consulta(
                    "SELECT 1 FROM rec_medios WHERE escena_id = ? AND punto_id IS NULL AND tipo = 'imagen'", [$escenaId]
                )->fetchColumn();
                if ($tipo !== 'imagen' || $portada) {
                    throw new DomainException('La escena completa admite una imagen. Carga los demás archivos en un punto.');
                }
            } elseif (!rec_consulta('SELECT 1 FROM rec_puntos WHERE id = ? AND escena_id = ?', [$puntoId, $escenaId])->fetchColumn()) {
                throw new DomainException('Elige un punto de esta escena.');
            }
            rec_consulta('INSERT INTO rec_medios (escena_id, punto_id, tipo, nombre_archivo) VALUES (?, ?, ?, ?)',
                [$escenaId, $puntoId, $tipo, $nombre]);
        });
    } catch (Throwable $error) {
        // Una carga fallida no deja el archivo suelto en el disco.
        rec_limpiar_medio($nombre);
        throw $error;
    }
}

function rec_eliminar_medio(array $escena, ?int $id): void
{
    $nombre = rec_transaccion((int)$escena['recorrido_id'], function (array $recorrido) use ($escena, $id): string {
        $medio = rec_consulta('SELECT * FROM rec_medios WHERE id = ? AND escena_id = ?', [$id, $escena['id']])->fetch();
        if (!$medio) {
            throw new DomainException('El medio ya no está en esta escena.');
        }
        rec_consulta('DELETE FROM rec_medios WHERE id = ?', [$id]);
        $principal = $medio['punto_id'] === null && in_array($medio['tipo'], ['imagen', 'video'], true);
        if ($principal && $recorrido['estado'] === 'publicado' && !rec_tiene_medio_principal((int)$recorrido['id'])) {
            throw new DomainException('Archiva el recorrido antes de quitar su última imagen o video principal.');
        }
        return $medio['nombre_archivo'];
    });
    rec_limpiar_medio($nombre);
}

// Las llaves foráneas borran los registros hijos. El código retira los archivos del disco.
function rec_eliminar_con_medios(int $recorridoId, string $tipo, int $id): void
{
    $archivos = rec_transaccion($recorridoId, function (array $recorrido) use ($tipo, $id, $recorridoId): array {
        if ($tipo !== 'punto' && $recorrido['estado'] === 'publicado') {
            throw new DomainException('Archiva el recorrido antes de eliminarlo o quitar una escena.');
        }
        [$buscar, $borrar] = match ($tipo) {
            'recorrido' => [
                'SELECT m.nombre_archivo FROM rec_medios m JOIN rec_escenas e ON e.id = m.escena_id WHERE e.recorrido_id = ?',
                'DELETE FROM rec_recorridos WHERE id = ?',
            ],
            'escena' => ['SELECT nombre_archivo FROM rec_medios WHERE escena_id = ?', 'DELETE FROM rec_escenas WHERE id = ?'],
            'punto' => ['SELECT nombre_archivo FROM rec_medios WHERE punto_id = ?', 'DELETE FROM rec_puntos WHERE id = ?'],
        };
        $archivos = rec_consulta($buscar, [$id])->fetchAll(PDO::FETCH_COLUMN);
        rec_consulta($borrar, [$id]);
        if ($tipo === 'escena') {
            rec_renumerar($recorridoId);
        }
        return $archivos;
    });
    foreach ($archivos as $archivo) {
        rec_limpiar_medio($archivo);
    }
}

function rec_limpiar_medio(string $nombre): void
{
    try {
        $ruta = rec_ruta_medio($nombre);
        if (is_file($ruta) && !@unlink($ruta)) {
            error_log('REC: no se pudo eliminar el archivo ' . $nombre);
        }
    } catch (RuntimeException $error) {
        error_log('REC: ' . $error->getMessage());
    }
}
