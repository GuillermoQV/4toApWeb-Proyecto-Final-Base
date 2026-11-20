<?php

declare(strict_types=1);

const REC_TIPOS_MEDIO = [
    'imagen' => ['mime' => SOLO_IMAGEN, 'limite' => 5],
    'video' => ['mime' => SOLO_VIDEO, 'limite' => 30],
    'audio' => ['mime' => ['audio/mpeg', 'audio/mp4'], 'limite' => 10],
];

function rec_carpeta_medios(): string
{
    $raiz = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($raiz === false || dirname($raiz) === $raiz) {
        throw new RuntimeException('No se pudo determinar la carpeta de medios.');
    }

    // En XAMPP la carpeta queda en C:/xampp/ev-rec-medios, fuera de htdocs.
    $carpeta = dirname($raiz) . DIRECTORY_SEPARATOR . 'ev-rec-medios';
    if (file_exists($carpeta) || is_link($carpeta)) {
        $real = realpath($carpeta);
        if ($real === false || !is_dir($real)) {
            throw new RuntimeException('La carpeta de medios no está disponible.');
        }
        $normalizar = static function (string $ruta): string {
            $ruta = str_replace('\\', '/', $ruta);
            return DIRECTORY_SEPARATOR === '\\' ? strtolower($ruta) : $ruta;
        };
        if ($normalizar($real) !== $normalizar($carpeta)) {
            throw new RuntimeException('La carpeta de medios debe estar fuera de la raíz web.');
        }
    }
    return $carpeta;
}

function rec_contexto_escena(int $escenaId): ?array
{
    $consulta = bd()->prepare(
        'SELECT e.id, e.titulo, r.id AS recorrido_id, r.carrera_id
         FROM rec_escenas e JOIN rec_recorridos r ON r.id = e.recorrido_id
         WHERE e.id = ?'
    );
    $consulta->execute([$escenaId]);
    return $consulta->fetch() ?: null;
}

function rec_puntos_de_escena(int $escenaId): array
{
    $consulta = bd()->prepare('SELECT id, titulo FROM rec_puntos WHERE escena_id = ? ORDER BY id');
    $consulta->execute([$escenaId]);
    return $consulta->fetchAll();
}

function rec_medios_de_escena(int $escenaId): array
{
    $consulta = bd()->prepare(
        'SELECT m.id, m.tipo, p.titulo AS punto
         FROM rec_medios m LEFT JOIN rec_puntos p ON p.id = m.punto_id
         WHERE m.escena_id = ? ORDER BY m.id'
    );
    $consulta->execute([$escenaId]);
    return $consulta->fetchAll();
}

function rec_obtener_medio(int $medioId): ?array
{
    $consulta = bd()->prepare(
        'SELECT m.id, m.tipo, m.nombre_archivo, r.estado, r.carrera_id
         FROM rec_medios m
         JOIN rec_escenas e ON e.id = m.escena_id
         JOIN rec_recorridos r ON r.id = e.recorrido_id
         WHERE m.id = ?'
    );
    $consulta->execute([$medioId]);
    return $consulta->fetch() ?: null;
}

function rec_registrar_medio(int $escenaId, int $carreraId, ?int $puntoId, string $tipo, string $nombre): bool
{
    $db = bd();
    $db->beginTransaction();
    try {
        $lock = $db->prepare('SELECT e.id FROM rec_escenas e JOIN rec_recorridos r ON r.id=e.recorrido_id
                              WHERE e.id=? AND r.carrera_id=? FOR UPDATE');
        $lock->execute([$escenaId, $carreraId]);
        if (!$lock->fetchColumn()) {
            throw new DomainException('La escena ya no está disponible.');
        }
        if ($puntoId === null) {
            if ($tipo !== 'imagen') {
                throw new DomainException('La escena completa admite una imagen. Asigna los videos a un punto.');
            }
            $q = $db->prepare("SELECT id FROM rec_medios WHERE escena_id=? AND punto_id IS NULL AND tipo='imagen' LIMIT 1");
            $q->execute([$escenaId]);
            if ($q->fetchColumn()) {
                throw new DomainException('Esta escena ya tiene una imagen. Elimínala antes de subir otra.');
            }
        }
        $q = $db->prepare('INSERT INTO rec_medios (escena_id,punto_id,tipo,nombre_archivo) VALUES (?,?,?,?)');
        $q->execute([$escenaId, $puntoId, $tipo, $nombre]);
        $db->commit();
        return true;
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
}

function rec_eliminar_medio(int $medioId, int $escenaId, int $carreraId): void
{
    $db = bd();
    $db->beginTransaction();
    try {
        $q = $db->prepare('SELECT m.nombre_archivo FROM rec_medios m JOIN rec_escenas e ON e.id=m.escena_id
            JOIN rec_recorridos r ON r.id=e.recorrido_id WHERE m.id=? AND e.id=? AND r.carrera_id=? FOR UPDATE');
        $q->execute([$medioId, $escenaId, $carreraId]);
        $nombre = $q->fetchColumn();
        if ($nombre === false) {
            throw new DomainException('El medio ya no está en esta escena.');
        }
        $q = $db->prepare('DELETE FROM rec_medios WHERE id=? AND escena_id=?');
        $q->execute([$medioId, $escenaId]);
        $db->commit();
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
    rec_limpiar_medio($nombre);
}

function rec_ruta_medio(string $nombre): string
{
    if (!preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp|mp4|mp3|m4a)\z/', $nombre)) {
        throw new RuntimeException('Nombre interno de archivo inválido.');
    }
    return rec_carpeta_medios() . DIRECTORY_SEPARATOR . $nombre;
}

function rec_limpiar_medio(string $nombre): void
{
    $ruta = rec_ruta_medio($nombre);
    if (is_file($ruta) && !@unlink($ruta)) {
        error_log('REC: no se pudo eliminar el archivo ' . $nombre);
    }
}
