-- Selecciona antes la base de datos indicada en api/.env (DB_NAME).

-- tabla lector
CREATE TABLE IF NOT EXISTS lector(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CI VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- tabla autor
CREATE TABLE IF NOT EXISTS autor(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CI VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    biografia TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'Lector',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- tabla libro / publicacion
CREATE TABLE IF NOT EXISTS libro(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    titulo VARCHAR(150) NOT NULL,
    sinopsis TEXT,
    portada VARCHAR(2048),
    genero VARCHAR(100),
    estado_publicacion ENUM('En Redacción', 'En Revisión', 'Publicado', 'Archivado') NOT NULL DEFAULT 'En Redacción',
    fecha_publicacion DATE,
    cod_usuario INT NULL,
    INDEX idx_libro_cod_usuario (cod_usuario),
    FOREIGN KEY (cod_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- tabla relacional autor_libro
CREATE TABLE IF NOT EXISTS autor_libro(
    cod_libro INT NOT NULL,
    cod_autor INT NOT NULL,
    tipo_participacion VARCHAR(50) DEFAULT 'Autor Principal',

    PRIMARY KEY(cod_libro, cod_autor),
    FOREIGN KEY(cod_libro) REFERENCES libro(id) ON DELETE CASCADE,
    FOREIGN KEY(cod_autor) REFERENCES autor(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- tabla lectura_libro
CREATE TABLE IF NOT EXISTS lectura_libro(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    cod_lector INT NOT NULL,
    cod_libro INT NOT NULL,
    fecha_inicio DATE NOT NULL DEFAULT (CURRENT_DATE),

    FOREIGN KEY(cod_lector) REFERENCES lector(id) ON DELETE CASCADE,
    FOREIGN KEY(cod_libro) REFERENCES libro(id) ON DELETE CASCADE
) ENGINE=InnoDB;