-- Importar después de sql/01-nucleo.sql en phpMyAdmin o MariaDB.
USE explorador_vocacional;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS rec_recorridos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carrera_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('borrador', 'publicado', 'archivado') NOT NULL DEFAULT 'borrador',
    KEY rec_ix_carrera_estado (carrera_id, estado),
    CONSTRAINT rec_fk_recorrido_carrera
        FOREIGN KEY (carrera_id) REFERENCES carreras(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rec_escenas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recorrido_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT NOT NULL,
    orden INT UNSIGNED NOT NULL,
    KEY rec_ix_escena_orden (recorrido_id, orden),
    CONSTRAINT rec_fk_escena_recorrido
        FOREIGN KEY (recorrido_id) REFERENCES rec_recorridos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rec_puntos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    escena_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT NOT NULL,
    x DECIMAL(5,2) NOT NULL,
    y DECIMAL(5,2) NOT NULL,
    UNIQUE KEY rec_uq_punto_escena (id, escena_id),
    CONSTRAINT rec_ck_punto_x CHECK (x BETWEEN 0 AND 100),
    CONSTRAINT rec_ck_punto_y CHECK (y BETWEEN 0 AND 100),
    CONSTRAINT rec_fk_punto_escena
        FOREIGN KEY (escena_id) REFERENCES rec_escenas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rec_medios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    escena_id INT UNSIGNED NOT NULL,
    punto_id INT UNSIGNED NULL,
    tipo ENUM('imagen', 'video', 'audio') NOT NULL,
    nombre_archivo VARCHAR(80) NOT NULL,
    UNIQUE KEY rec_uq_medio_nombre (nombre_archivo),
    CONSTRAINT rec_fk_medio_escena
        FOREIGN KEY (escena_id) REFERENCES rec_escenas(id) ON DELETE CASCADE,
    CONSTRAINT rec_fk_medio_punto
        FOREIGN KEY (punto_id, escena_id) REFERENCES rec_puntos(id, escena_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
