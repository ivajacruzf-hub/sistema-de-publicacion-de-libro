DROP DATABASE IF EXISTS dbpublicaciones;
CREATE DATABASE dbpublicaciones;
USE dbpublicaciones;

-- tabla cliente
CREATE TABLE CLIENTE(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CI VARCHAR(20) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    direccion VARCHAR(250),
    telefono VARCHAR(15) NOT NULL
) ENGINE=InnoDB;

-- tabla pedido
CREATE TABLE pedido(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    cod_cliente INT NOT NULL,
    fecha_compra DATETIME NOT NULL,
    cantidad INT NOT NULL,

    FOREIGN KEY(cod_cliente) REFERENCES CLIENTE(id)

) ENGINE=InnoDB;
