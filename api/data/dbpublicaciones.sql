-- DROP DATABASE IF EXISTS dbpublicacion;
-- CREATE DATABASE dbpublicacion;
USE defaultdb;

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
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Se guarda la contraseña encriptada
    rol VARCHAR(20) DEFAULT 'Usuario',
    estado VARCHAR(15) DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB;
-- tabla libro / publicacion
CREATE TABLE IF NOT EXISTS libro(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    titulo VARCHAR(150) NOT NULL,
    sinopsis TEXT,
    estado_publicacion ENUM('En Redacción', 'En Revisión', 'Publicado', 'Archivado') NOT NULL DEFAULT 'En Redacción',
    fecha_publicacion DATE
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