	-- Debe coincidir con DB_NAME en api/.env.
	-- No se elimina la base: conserva las demás tablas del proyecto.
	CREATE DATABASE IF NOT EXISTS dbpublicaciones;
	USE dbpublicaciones;
	-- crear tabla de usuario
	CREATE TABLE usuarios(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	nombreUsuario VARCHAR(100) NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL
	)ENGINE=InnoDB;
	-- crea la tabla de imagenes
	CREATE TABLE imagenes(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	urlImagen VARCHAR(255) NOT NULL,
	usuarioId INT NOT NULL,
	CONSTRAINT fk_user_imagen FOREIGN KEY(usuarioId) REFERENCES usuarios(id)
	)ENGINE=InnoDB;
	
	-- adicionar datos a la tabla usuarios
	INSERT INTO usuarios(nombreUsuario,email,password)
	values('Roberto','Roberto@gmail.com','robert123');
	
	INSERT INTO usuarios(nombreUsuario,email,password)
	values('Maria','maria@gmail.com','maria123');
	
	
