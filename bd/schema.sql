-- ============================================================
-- Módulo de Licitaciones
-- Base de datos: licitaciones_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS licitaciones_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE licitaciones_db;

-- ------------------------------------------------------------
-- Tabla: actividades
-- Fuente: UNSPSC clasificador de bienes y servicios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS actividades (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_segmento  INT          NOT NULL,
    segmento         VARCHAR(200) NOT NULL,
    codigo_familia   INT          NOT NULL,
    familia          VARCHAR(200) NOT NULL,
    codigo_clase     INT          NOT NULL,
    clase            VARCHAR(200) NOT NULL,
    codigo_producto  INT          NOT NULL,
    producto         VARCHAR(200) NOT NULL,
    INDEX idx_segmento (codigo_segmento),
    INDEX idx_familia  (codigo_familia),
    INDEX idx_clase    (codigo_clase)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE actividades
    ADD FULLTEXT INDEX ft_actividades_busqueda (producto, clase, familia, segmento);

-- ------------------------------------------------------------
-- Tabla: ofertas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ofertas (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consecutivo   VARCHAR(20)    NOT NULL UNIQUE,
    objeto        VARCHAR(150)   NOT NULL,
    descripcion   VARCHAR(400)   NOT NULL,
    moneda        VARCHAR(3)     NOT NULL COMMENT 'COP | USD | EUR',
    presupuesto   DECIMAL(15,2)  NOT NULL,
    actividad_id  INT UNSIGNED   NOT NULL,
    fecha_inicio  DATE           NOT NULL,
    hora_inicio   TIME           NOT NULL,
    fecha_cierre  DATE           NOT NULL,
    hora_cierre   TIME           NOT NULL,
    estado        VARCHAR(20)    NOT NULL DEFAULT 'activo',
    creado_en     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_oferta_actividad
        FOREIGN KEY (actividad_id)
        REFERENCES actividades (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    INDEX idx_consecutivo (consecutivo),
    INDEX idx_estado      (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: ofertas_documentos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ofertas_documentos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    licitacion_id INT UNSIGNED   NOT NULL,
    titulo        VARCHAR(100)   NOT NULL,
    descripcion   VARCHAR(200)   NOT NULL,
    archivo       VARCHAR(500)   NOT NULL COMMENT 'Ruta relativa al archivo PDF/ZIP',
    creado_en     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documento_oferta
        FOREIGN KEY (licitacion_id)
        REFERENCES ofertas (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_licitacion (licitacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;